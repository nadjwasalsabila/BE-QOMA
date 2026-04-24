<?php

namespace App\Http\Controllers\Api\Owner;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\Usaha;
use App\Services\MenuService;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function __construct(private MenuService $service) {}

    // GET /owner/usaha/{usaha_id}/menu?kategori_id=xxx&is_active=1
    public function index(Request $request, string $usahaId)
    {
        $this->authorizeUsaha($usahaId);

        $menus = $this->service->getByUsaha($usahaId, $request->only(['kategori_id', 'is_active']));

        return response()->json([
            'message' => 'Daftar menu',
            'data'    => $menus,
        ]);
    }

    // POST /owner/usaha/{usaha_id}/menu  (multipart/form-data)
    public function store(Request $request, string $usahaId)
    {
        $this->authorizeUsaha($usahaId);

        $request->validate([
            'kategori_id'         => 'required|exists:kategori_menu,id',
            'nama'                => 'required|string|max:150',
            'harga_default'       => 'required|numeric|min:0',
            'keterangan'          => 'nullable|string',
            'is_active'           => 'nullable|boolean',
            'gambar'              => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048', // max 2MB
            // bahan_baku dikirim sebagai array JSON
            // contoh: bahan_baku[0][bahan_baku_id]=xxx&bahan_baku[0][jumlah_pakai]=200
            'bahan_baku'          => 'nullable|array',
            'bahan_baku.*.bahan_baku_id' => 'required|exists:bahan_baku,id',
            'bahan_baku.*.jumlah_pakai'  => 'required|numeric|min:0',
        ]);

        $menu = $this->service->create(
            $request->except('gambar'),
            $usahaId,
            $request->file('gambar')
        );

        return response()->json([
            'message' => 'Menu berhasil dibuat dan sudah tersebar ke semua cabang',
            'data'    => $menu,
        ], 201);
    }

    // GET /owner/usaha/{usaha_id}/menu/{id}
    public function show(string $usahaId, string $id)
    {
        $this->authorizeUsaha($usahaId);
        $menu = $this->findOwned($usahaId, $id);

        return response()->json([
            'message' => 'Detail menu',
            'data'    => $menu->load(['kategori', 'bahanBakus', 'menuTenants.tenant']),
        ]);
    }

    // POST /owner/usaha/{usaha_id}/menu/{id}?_method=PUT  (karena multipart tidak support PUT)
    public function update(Request $request, string $usahaId, string $id)
    {
        $this->authorizeUsaha($usahaId);

        $request->validate([
            'kategori_id'                => 'sometimes|exists:kategori_menu,id',
            'nama'                       => 'sometimes|string|max:150',
            'harga_default'              => 'sometimes|numeric|min:0',
            'keterangan'                 => 'nullable|string',
            'is_active'                  => 'nullable|boolean',
            'gambar'                     => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'bahan_baku'                 => 'nullable|array',
            'bahan_baku.*.bahan_baku_id' => 'required|exists:bahan_baku,id',
            'bahan_baku.*.jumlah_pakai'  => 'required|numeric|min:0',
        ]);

        $menu = $this->findOwned($usahaId, $id);
        $menu = $this->service->update($menu, $request->except('gambar'), $request->file('gambar'));

        return response()->json([
            'message' => 'Menu berhasil diupdate',
            'data'    => $menu,
        ]);
    }

    // DELETE /owner/usaha/{usaha_id}/menu/{id}
    public function destroy(string $usahaId, string $id)
    {
        $this->authorizeUsaha($usahaId);
        $menu = $this->findOwned($usahaId, $id);
        $this->service->delete($menu);

        return response()->json(['message' => 'Menu berhasil dihapus']);
    }

    private function authorizeUsaha(string $usahaId): void
    {
        $ok = Usaha::where('id', $usahaId)->where('owner_id', auth()->id())->exists();
        if (!$ok) abort(403, 'Usaha bukan milik Anda');
    }

    private function findOwned(string $usahaId, string $id): Menu
    {
        $menu = Menu::where('id', $id)->where('usaha_id', $usahaId)->first();
        if (!$menu) abort(404, 'Menu tidak ditemukan');
        return $menu;
    }
}