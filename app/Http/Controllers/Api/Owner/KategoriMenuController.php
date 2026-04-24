<?php

namespace App\Http\Controllers\Api\Owner;

use App\Http\Controllers\Controller;
use App\Models\KategoriMenu;
use App\Services\KategoriMenuService;
use Illuminate\Http\Request;

class KategoriMenuController extends Controller
{
    public function __construct(private KategoriMenuService $service) {}

    // GET /owner/usaha/{usaha_id}/kategori
    public function index(string $usahaId)
    {
        $this->authorizeUsaha($usahaId);

        return response()->json([
            'message' => 'Daftar kategori',
            'data'    => $this->service->getByUsaha($usahaId),
        ]);
    }

    // POST /owner/usaha/{usaha_id}/kategori
    public function store(Request $request, string $usahaId)
    {
        $this->authorizeUsaha($usahaId);

        $request->validate([
            'nama' => 'required|string|max:100',
        ]);

        $kategori = $this->service->create($request->all(), $usahaId);

        return response()->json([
            'message' => 'Kategori berhasil dibuat',
            'data'    => $kategori,
        ], 201);
    }

    // PUT /owner/usaha/{usaha_id}/kategori/{id}
    public function update(Request $request, string $usahaId, string $id)
    {
        $this->authorizeUsaha($usahaId);

        $request->validate(['nama' => 'required|string|max:100']);

        $kategori = $this->findOwned($usahaId, $id);
        $kategori = $this->service->update($kategori, $request->all());

        return response()->json([
            'message' => 'Kategori berhasil diupdate',
            'data'    => $kategori,
        ]);
    }

    // DELETE /owner/usaha/{usaha_id}/kategori/{id}
    public function destroy(string $usahaId, string $id)
    {
        $this->authorizeUsaha($usahaId);

        $kategori = $this->findOwned($usahaId, $id);

        try {
            $this->service->delete($kategori);
            return response()->json(['message' => 'Kategori berhasil dihapus']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    private function authorizeUsaha(string $usahaId): void
    {
        $ok = \App\Models\Usaha::where('id', $usahaId)
                               ->where('owner_id', auth()->id())
                               ->exists();
        if (!$ok) abort(403, 'Usaha bukan milik Anda');
    }

    private function findOwned(string $usahaId, string $id): KategoriMenu
    {
        $k = KategoriMenu::where('id', $id)->where('usaha_id', $usahaId)->first();
        if (!$k) abort(404, 'Kategori tidak ditemukan');
        return $k;
    }
}