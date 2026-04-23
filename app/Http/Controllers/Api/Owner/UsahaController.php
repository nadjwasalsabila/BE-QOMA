<?php

namespace App\Http\Controllers\Api\Owner;

use App\Http\Controllers\Controller;
use App\Models\Usaha;
use App\Services\UsahaService;
use Illuminate\Http\Request;
use App\Traits\HasPagination;

class UsahaController extends Controller
{
    use HasPagination;

    public function index(Request $request)
    {
        $usahas = Usaha::where('owner_id', auth()->id())
                       ->withCount('tenants')
                       ->paginate($this->getPerPage($request));

        return response()->json(
            $this->paginateResponse($usahas, 'Daftar usaha')
        );
    }
}

class UsahaController extends Controller
{
    public function __construct(private UsahaService $usahaService) {}

    // GET /owner/usaha — list semua usaha milik owner ini
    public function index()
    {
        $usahas = $this->usahaService->getByOwner(auth()->id());

        return response()->json([
            'message' => 'Daftar usaha',
            'data'    => $usahas,
        ]);
    }

    // POST /owner/usaha — buat usaha baru
    public function store(Request $request)
    {
        $request->validate([
            'nama_usaha' => 'required|string|max:100',
            'email'      => 'nullable|email',
        ]);

        $usaha = $this->usahaService->create($request->all(), auth()->id());

        return response()->json([
            'message' => 'Usaha berhasil dibuat',
            'data'    => $usaha,
        ], 201);
    }

    // GET /owner/usaha/{id} — detail 1 usaha
    public function show(Usaha $usaha)
    {
        $this->authorize('view', $usaha);

    return response()->json($usaha);
}

    // PUT /owner/usaha/{id} — update usaha
   public function update(Request $request, string $id)
{
    $request->validate([
        'nama_usaha' => 'sometimes|string|max:100',
        'email'      => 'nullable|email',
    ]);

    $usaha = Usaha::findOrFail($id);

    $this->authorize('update', $usaha);

    $usaha = $this->usahaService->update($usaha, $request->all());

    return response()->json([
        'message' => 'Usaha berhasil diupdate',
        'data'    => $usaha,
    ]);
}

    // DELETE /owner/usaha/{id} — hapus usaha
    public function destroy(string $id)
    {
        $usaha = $this->findOwned($id);
        $this->usahaService->delete($usaha);

        return response()->json(['message' => 'Usaha beserta seluruh cabang berhasil dihapus']);
    }

    // Helper: cari usaha dan pastikan milik owner yang login
    private function findOwned(string $id): Usaha
    {
        $usaha = Usaha::where('id', $id)
                      ->where('owner_id', auth()->id())
                      ->first();

        if (!$usaha) {
            abort(404, 'Usaha tidak ditemukan atau bukan milik Anda');
        }

        return $usaha;
    }
}