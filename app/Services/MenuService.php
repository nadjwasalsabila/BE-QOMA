<?php

namespace App\Services;

use App\Models\Menu;
use App\Models\MenuBahanBaku;
use App\Models\MenuTenant;
use App\Models\Tenant;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MenuService
{
    /**
     * Ambil semua menu milik usaha, dengan relasi lengkap
     */
    public function getByUsaha(string $usahaId, array $filters = [])
    {
        
        $query = Menu::where('usaha_id', $usahaId)
                     ->with(['kategori', 'bahanBakus', 'menuTenants.tenant']);

        if (!empty($filters['kategori_id'])) {
            $query->where('kategori_id', $filters['kategori_id']);
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        return $query->orderBy('nama')->get();
    }

    /**
     * Buat menu baru
     *
     * Flow:
     * 1. Upload gambar jika ada
     * 2. Insert ke tabel menu
     * 3. Sync bahan_baku ke menu_bahan_baku
     * 4. Auto insert ke menu_tenant untuk SEMUA cabang usaha ini
     */
    public function create(array $data, string $usahaId, ?UploadedFile $gambar = null): Menu
    {
        return DB::transaction(function () use ($data, $usahaId, $gambar) {

            // 1. Upload gambar
            $gambarPath = null;
            if ($gambar) {
                $gambarPath = $this->uploadGambar($gambar, $usahaId);
            }

            // 2. Buat menu
            $menu = Menu::create([
                'id'            => Str::uuid(),
                'usaha_id'      => $usahaId,
                'kategori_id'   => $data['kategori_id'],
                'nama'          => $data['nama'],
                'harga_default' => $data['harga_default'],
                'gambar'        => $gambarPath,
                'keterangan'    => $data['keterangan'] ?? null,
                'is_active'     => $data['is_active'] ?? true,
            ]);

            // 3. Sync bahan baku
            if (!empty($data['bahan_baku'])) {
                $this->syncBahanBaku($menu, $data['bahan_baku']);
            }

            // 4. Auto-generate menu_tenant untuk semua cabang usaha ini
            $this->syncMenuTenantSemua($menu, $usahaId);

            return $menu->load(['kategori', 'bahanBakus', 'menuTenants.tenant']);
        });
    }

    /**
     * Update menu
     *
     * Flow:
     * 1. Ganti gambar jika ada gambar baru
     * 2. Update data menu
     * 3. Sync ulang bahan baku
     * 4. Jika harga_default berubah → update menu_tenant yang masih pakai harga lama
     */
    public function update(Menu $menu, array $data, ?UploadedFile $gambar = null): Menu
    {
        return DB::transaction(function () use ($menu, $data, $gambar) {

            $oldHarga = $menu->harga_default;

            // 1. Ganti gambar jika ada
            if ($gambar) {
                // Hapus gambar lama
                if ($menu->gambar) {
                    Storage::disk('public')->delete($menu->gambar);
                }
                $data['gambar'] = $this->uploadGambar($gambar, $menu->usaha_id);
            }

            // 2. Update menu
            $menu->update([
                'kategori_id'   => $data['kategori_id']   ?? $menu->kategori_id,
                'nama'          => $data['nama']           ?? $menu->nama,
                'harga_default' => $data['harga_default']  ?? $menu->harga_default,
                'gambar'        => $data['gambar']         ?? $menu->gambar,
                'keterangan'    => $data['keterangan']     ?? $menu->keterangan,
                'is_active'     => $data['is_active']      ?? $menu->is_active,
            ]);

            // 3. Sync bahan baku jika dikirim
            if (isset($data['bahan_baku'])) {
                $this->syncBahanBaku($menu, $data['bahan_baku']);
            }

            // 4. Jika harga_default berubah, update menu_tenant
            //    yang harganya masih sama dengan harga lama (belum di-override cabang)
            $newHarga = $menu->fresh()->harga_default;
            if ((float)$oldHarga !== (float)$newHarga) {
                MenuTenant::where('menu_id', $menu->id)
                          ->where('harga', $oldHarga) // hanya yang belum di-override
                          ->update(['harga' => $newHarga]);
            }

            return $menu->fresh(['kategori', 'bahanBakus', 'menuTenants.tenant']);
        });
    }

    /**
     * Hapus menu beserta gambar dan relasi
     */
    public function delete(Menu $menu): void
    {
        DB::transaction(function () use ($menu) {
            if ($menu->gambar) {
                Storage::disk('public')->delete($menu->gambar);
            }
            // cascade di DB akan hapus menu_bahan_baku dan menu_tenant otomatis
            $menu->delete();
        });
    }

    /**
     * Saat CABANG BARU dibuat → auto insert menu_tenant
     * untuk semua menu yang sudah ada di usaha tersebut
     *
     * Dipanggil dari TenantService::create()
     */
    public function syncMenuTenantUntukCabangBaru(string $tenantId, string $usahaId): void
    {
        $menus = Menu::where('usaha_id', $usahaId)->get();

        foreach ($menus as $menu) {
            MenuTenant::firstOrCreate(
                ['menu_id' => $menu->id, 'tenant_id' => $tenantId],
                [
                    'id'           => Str::uuid(),
                    'harga'        => $menu->harga_default,
                    'is_available' => true,
                ]
            );
        }
    }

    // ===========================
    // PRIVATE HELPERS
    // ===========================

    /**
     * Upload gambar ke storage/public/menu/{usaha_id}/
     * Return path relatif untuk disimpan di DB
     */
    private function uploadGambar(UploadedFile $file, string $usahaId): string
    {
        $filename  = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $directory = "menu/{$usahaId}";

        // Simpan ke storage/app/public/menu/{usaha_id}/
        $path = $file->storeAs($directory, $filename, 'public');

        return $path; // contoh: "menu/uuid-usaha/uuid-file.jpg"
    }

    /**
     * Sync tabel menu_bahan_baku
     *
     * $bahanBakuData = [
     *   ['bahan_baku_id' => 'xxx', 'jumlah_pakai' => 200],
     *   ['bahan_baku_id' => 'yyy', 'jumlah_pakai' => 50],
     * ]
     */
    private function syncBahanBaku(Menu $menu, array $bahanBakuData): void
    {
        // Hapus relasi lama dulu
        MenuBahanBaku::where('menu_id', $menu->id)->delete();

        // Insert baru
        foreach ($bahanBakuData as $item) {
            MenuBahanBaku::create([
                'id'            => Str::uuid(),
                'menu_id'       => $menu->id,
                'bahan_baku_id' => $item['bahan_baku_id'],
                'jumlah_pakai'  => $item['jumlah_pakai'],
            ]);
        }
    }

    /**
     * Auto insert menu_tenant untuk semua cabang usaha ini
     * Dipanggil saat menu baru dibuat
     */
    private function syncMenuTenantSemua(Menu $menu, string $usahaId): void
    {
        $tenants = Tenant::where('usaha_id', $usahaId)->get();

        foreach ($tenants as $tenant) {
            MenuTenant::firstOrCreate(
                ['menu_id' => $menu->id, 'tenant_id' => $tenant->id],
                [
                    'id'           => Str::uuid(),
                    'harga'        => $menu->harga_default,
                    'is_available' => true,
                ]
            );
        }
    }
}