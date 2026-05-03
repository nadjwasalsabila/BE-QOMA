<?php

namespace App\Http\Controllers\Api\Owner;

use App\Http\Controllers\Controller;
use App\Models\BahanMaster;
use App\Services\ActivityLogService;
use App\Traits\{HasPagination, OwnerAccess};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Storage, DB};
use Illuminate\Support\Str;

class BahanMasterController extends Controller
{
    use HasPagination, OwnerAccess;

    // GET /owner/bahan-baku?search=beras&page=1
    public function index(Request $request)
    {
        $usahaId = $this->getUsahaId();

        $query = BahanMaster::where('usaha_id', $usahaId);

        if ($request->search) {
            $query->where('nama', 'like', "%{$request->search}%");
        }

        $bahans = $query->orderBy('nama')
                        ->paginate($this->getPerPage($request));

        return response()->json($this->paginateResponse($bahans, 'Daftar bahan baku'));
    }

    // POST /owner/bahan-baku  (multipart/form-data)
    public function store(Request $request)
    {
        $usahaId = $this->getUsahaId();

        $request->validate([
            'nama'          => 'required|string|max:100',
            'satuan'        => 'required|string|max:20',  // kg, liter, pcs, dll
            'harga_default' => 'required|numeric|min:0',
            'gambar'        => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        // Cek duplikat nama dalam usaha yang sama
        $duplikat = BahanMaster::where('usaha_id', $usahaId)
                               ->where('nama', $request->nama)
                               ->exists();

        if ($duplikat) {
            return response()->json([
                'message' => "Bahan baku '{$request->nama}' sudah ada.",
                'code'    => 'DUPLICATE',
            ], 422);
        }

        $gambarPath = null;
        if ($request->hasFile('gambar')) {
            $gambarPath = $request->file('gambar')->storeAs(
                "bahan-master/{$usahaId}",
                Str::uuid() . '.' . $request->file('gambar')->getClientOriginalExtension(),
                'public'
            );
        }

        $bahan = BahanMaster::create([
            'id'            => Str::uuid(),
            'usaha_id'      => $usahaId,
            'nama'          => $request->nama,
            'satuan'        => $request->satuan,
            'harga_default' => $request->harga_default,
            'gambar'        => $gambarPath,
        ]);

        ActivityLogService::log(
            'create_bahan_master',
            "Bahan baku '{$bahan->nama}' (Rp " . number_format($bahan->harga_default) . "/{$bahan->satuan}) ditambahkan",
            ['bahan_id' => $bahan->id, 'nama' => $bahan->nama],
            $usahaId,
        );

        return response()->json([
            'message' => 'Bahan baku berhasil ditambahkan',
            'data'    => $bahan,
        ], 201);
    }

    // GET /owner/bahan-baku/{id}
    public function show(string $id)
    {
        $bahan = $this->validateMilikUsaha(BahanMaster::class, $id);

        return response()->json([
            'message' => 'Detail bahan baku',
            'data'    => $bahan,
        ]);
    }

    // PUT /owner/bahan-baku/{id}
    public function update(Request $request, string $id)
    {
        $usahaId = $this->getUsahaId();
        $bahan   = $this->validateMilikUsaha(BahanMaster::class, $id);

        $request->validate([
            'nama'          => 'sometimes|string|max:100',
            'satuan'        => 'sometimes|string|max:20',
            'harga_default' => 'sometimes|numeric|min:0',
            'gambar'        => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $gambarPath = $bahan->gambar;
        if ($request->hasFile('gambar')) {
            if ($bahan->gambar) Storage::disk('public')->delete($bahan->gambar);
            $gambarPath = $request->file('gambar')->storeAs(
                "bahan-master/{$usahaId}",
                Str::uuid() . '.' . $request->file('gambar')->getClientOriginalExtension(),
                'public'
            );
        }

        $bahan->update([
            'nama'          => $request->nama          ?? $bahan->nama,
            'satuan'        => $request->satuan        ?? $bahan->satuan,
            'harga_default' => $request->harga_default ?? $bahan->harga_default,
            'gambar'        => $gambarPath,
        ]);

        ActivityLogService::log(
            'update_bahan_master',
            "Bahan baku '{$bahan->nama}' diupdate",
            ['bahan_id' => $bahan->id],
            $usahaId,
        );

        return response()->json([
            'message' => 'Bahan baku berhasil diupdate',
            'data'    => $bahan->fresh(),
        ]);
    }

    // DELETE /owner/bahan-baku/{id}
    public function destroy(string $id)
    {
        $usahaId = $this->getUsahaId();
        $bahan   = $this->validateMilikUsaha(BahanMaster::class, $id);

        // Cek apakah bahan dipakai di menu
        $dipakaiDiMenu = DB::table('menu_bahan')
                           ->where('bahan_master_id', $id)
                           ->exists();

        if ($dipakaiDiMenu) {
            return response()->json([
                'message' => "Bahan '{$bahan->nama}' tidak bisa dihapus karena masih digunakan di menu.",
                'code'    => 'IN_USE',
            ], 422);
        }

        if ($bahan->gambar) Storage::disk('public')->delete($bahan->gambar);
        $bahan->delete();

        ActivityLogService::log(
            'delete_bahan_master',
            "Bahan baku '{$bahan->nama}' dihapus",
            ['bahan_id' => $id],
            $usahaId,
        );

        return response()->json(['message' => 'Bahan baku berhasil dihapus']);
    }
}