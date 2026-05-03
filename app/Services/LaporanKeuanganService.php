<?php

namespace App\Services;

use App\Models\{BahanMaster, LaporanKeuangan, StockOpname};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LaporanKeuanganService
{
    /**
     * Ambil atau buat record laporan untuk outlet + periode tertentu.
     * Ini adalah helper utama — semua method lain pakai ini.
     */
    private function getOrCreate(string $outletId, string $periode, string $tipe = 'daily'): LaporanKeuangan
    {
        return LaporanKeuangan::firstOrCreate(
            [
                'outlet_id'    => $outletId,
                'periode'      => $periode,
                'tipe_periode' => $tipe,
            ],
            [
                'id'                => Str::uuid(),
                'total_pendapatan'  => 0,
                'total_pengeluaran' => 0,
                'total_kerugian'    => 0,
                'total_keuntungan'  => 0,
            ]
        );
    }

    /**
     * Recalculate dan simpan semua komponen laporan untuk 1 outlet 1 hari.
     * Selalu hitung ulang dari scratch supaya tidak ada data stale.
     *
     * Dipanggil setiap kali ada event (pesanan paid, pengeluaran, stock opname).
     */
    public function recalculate(string $outletId, string $tanggal): LaporanKeuangan
    {
        return DB::transaction(function () use ($outletId, $tanggal) {

            // 1. Total pendapatan dari pesanan paid di hari ini
            $totalPendapatan = DB::table('pesanan')
                ->where('outlet_id', $outletId)
                ->where('status', 'paid')
                ->whereDate('updated_at', $tanggal)
                ->sum('total_harga');

            // 2. Total pengeluaran bahan baku di hari ini
            $totalPengeluaran = DB::table('pengeluaran')
                ->where('outlet_id', $outletId)
                ->where('tanggal', $tanggal)
                ->sum('total');

            // 3. Total kerugian dari 2 sumber:

            // 3a. Kerugian manual (input outlet)
            $kerugianManual = DB::table('kerugian')
                ->where('outlet_id', $outletId)
                ->where('tanggal', $tanggal)
                ->sum('total_rugi');

            // 3b. Kerugian dari stock opname (bahan rusak/busuk/hilang)
            //     Hitung: jumlah × harga_default bahan_master
            $kerugianStockOpname = DB::table('stock_opname')
                ->join('bahan_master', 'stock_opname.bahan_master_id', '=', 'bahan_master.id')
                ->where('stock_opname.outlet_id', $outletId)
                ->where('stock_opname.tipe', 'keluar') // keluar = rusak/busuk/hilang
                ->whereDate('stock_opname.created_at', $tanggal)
                ->sum(DB::raw('stock_opname.jumlah * bahan_master.harga_default'));

            $totalKerugian = $kerugianManual + $kerugianStockOpname;

            // 4. Keuntungan = pendapatan - pengeluaran - kerugian
            $totalKeuntungan = $totalPendapatan - $totalPengeluaran - $totalKerugian;

            // 5. Simpan / update laporan harian
            $laporan = $this->getOrCreate($outletId, $tanggal, 'daily');
            $laporan->update([
                'total_pendapatan'  => $totalPendapatan,
                'total_pengeluaran' => $totalPengeluaran,
                'total_kerugian'    => $totalKerugian,
                'total_keuntungan'  => $totalKeuntungan,
            ]);

            // 6. Update juga laporan bulanan (format: "2026-05")
            $bulan = substr($tanggal, 0, 7);
            $this->recalculateBulanan($outletId, $bulan);

            return $laporan->fresh();
        });
    }

    /**
     * Recalculate laporan bulanan dari aggregate laporan harian.
     */
    public function recalculateBulanan(string $outletId, string $bulan): LaporanKeuangan
    {
        $aggregate = LaporanKeuangan::where('outlet_id', $outletId)
            ->where('tipe_periode', 'daily')
            ->where('periode', 'like', "{$bulan}%")
            ->selectRaw('
                SUM(total_pendapatan)  as pendapatan,
                SUM(total_pengeluaran) as pengeluaran,
                SUM(total_kerugian)    as kerugian,
                SUM(total_keuntungan)  as keuntungan
            ')
            ->first();

        $laporan = $this->getOrCreate($outletId, $bulan, 'monthly');
        $laporan->update([
            'total_pendapatan'  => $aggregate->pendapatan  ?? 0,
            'total_pengeluaran' => $aggregate->pengeluaran ?? 0,
            'total_kerugian'    => $aggregate->kerugian    ?? 0,
            'total_keuntungan'  => $aggregate->keuntungan  ?? 0,
        ]);

        return $laporan->fresh();
    }

    /**
     * Ambil laporan untuk range tertentu (untuk dashboard & grafik).
     *
     * @param string $outletId
     * @param string $range    '1day' | '7days' | '30days'
     */
    public function getLaporan(string $outletId, string $range = '7days'): array
    {
        $query = LaporanKeuangan::where('outlet_id', $outletId)
                                ->where('tipe_periode', 'daily')
                                ->orderBy('periode');

        switch ($range) {
            case '1day':
                $query->where('periode', now()->toDateString());
                break;
            case '7days':
                $query->whereBetween('periode', [
                    now()->subDays(6)->toDateString(),
                    now()->toDateString(),
                ]);
                break;
            case '30days':
            default:
                $query->whereBetween('periode', [
                    now()->subDays(29)->toDateString(),
                    now()->toDateString(),
                ]);
                break;
        }

        $data = $query->get();

        // Summary total untuk card
        $summary = [
            'total_pendapatan'  => $data->sum('total_pendapatan'),
            'total_pengeluaran' => $data->sum('total_pengeluaran'),
            'total_kerugian'    => $data->sum('total_kerugian'),
            'total_keuntungan'  => $data->sum('total_keuntungan'),
            'status'            => $data->sum('total_keuntungan') >= 0 ? 'untung' : 'rugi',
        ];

        return [
            'range'   => $range,
            'summary' => $summary,
            'detail'  => $data, // untuk grafik per hari
        ];
    }

    /**
     * Ambil laporan semua outlet milik 1 usaha (untuk dashboard owner).
     */
    public function getLaporanByUsaha(string $usahaId, string $range = '30days'): array
    {
        // Ambil semua outlet_id milik usaha ini
        $outletIds = DB::table('outlet')
            ->where('usaha_id', $usahaId)
            ->pluck('id');

        if ($outletIds->isEmpty()) {
            return [
                'total_pendapatan'  => 0,
                'total_pengeluaran' => 0,
                'total_kerugian'    => 0,
                'total_keuntungan'  => 0,
                'per_outlet'        => [],
            ];
        }

        // Hitung range tanggal
        [$dari, $sampai] = match($range) {
            '1day'  => [now()->toDateString(), now()->toDateString()],
            '7days' => [now()->subDays(6)->toDateString(), now()->toDateString()],
            default => [now()->subDays(29)->toDateString(), now()->toDateString()],
        };

        // Aggregate semua outlet
        $global = LaporanKeuangan::whereIn('outlet_id', $outletIds)
            ->where('tipe_periode', 'daily')
            ->whereBetween('periode', [$dari, $sampai])
            ->selectRaw('
                SUM(total_pendapatan)  as total_pendapatan,
                SUM(total_pengeluaran) as total_pengeluaran,
                SUM(total_kerugian)    as total_kerugian,
                SUM(total_keuntungan)  as total_keuntungan
            ')
            ->first();

        // Per outlet (untuk grafik dropdown)
        $perOutlet = LaporanKeuangan::whereIn('outlet_id', $outletIds)
            ->where('tipe_periode', 'daily')
            ->whereBetween('periode', [$dari, $sampai])
            ->with('outlet:id,nama_outlet')
            ->orderBy('periode')
            ->get()
            ->groupBy('outlet_id')
            ->map(fn($rows, $outletId) => [
                'outlet_id'         => $outletId,
                'nama_outlet'       => $rows->first()->outlet->nama_outlet ?? '-',
                'total_pendapatan'  => $rows->sum('total_pendapatan'),
                'total_pengeluaran' => $rows->sum('total_pengeluaran'),
                'total_kerugian'    => $rows->sum('total_kerugian'),
                'total_keuntungan'  => $rows->sum('total_keuntungan'),
                'grafik'            => $rows->map(fn($r) => [
                    'tanggal'          => $r->periode,
                    'total_pendapatan' => $r->total_pendapatan,
                    'total_keuntungan' => $r->total_keuntungan,
                ]),
            ])
            ->values();

        return [
            'range'             => $range,
            'dari'              => $dari,
            'sampai'            => $sampai,
            'total_pendapatan'  => (float) ($global->total_pendapatan  ?? 0),
            'total_pengeluaran' => (float) ($global->total_pengeluaran ?? 0),
            'total_kerugian'    => (float) ($global->total_kerugian    ?? 0),
            'total_keuntungan'  => (float) ($global->total_keuntungan  ?? 0),
            'status'            => ($global->total_keuntungan ?? 0) >= 0 ? 'untung' : 'rugi',
            'per_outlet'        => $perOutlet,
        ];
    }
}