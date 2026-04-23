<?php

namespace App\Http\Controllers\Api\Owner;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\Usaha;
use App\Services\TenantService;
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

class TenantController extends Controller
{
    public function __construct(private TenantService $tenantService) {}

    // GET /owner/usaha/{usaha_id}/cabang
    public function index(string $usahaId)
    {
        $this->authorizeUsaha($usahaId);

        $tenants = $this->tenantService->getByUsaha($usahaId);

        return response()->json([
            'message' => 'Daftar cabang',
            'data'    => $tenants,
        ]);
    }

    // POST /owner/usaha/{usaha_id}/cabang
    public function store(Request $request, string $usahaId)
    {
        $this->authorizeUsaha($usahaId);

        $request->validate([
            'nama_cabang' => 'required|string|max:100',
            'alamat'      => 'nullable|string',
            'email_kasir' => 'nullable|email',
        ]);

        $result = $this->tenantService->create($request->all(), $usahaId);

        return response()->json([
            'message' => 'Cabang berhasil dibuat. Akun kasir otomatis dibuat.',
            'data'    => $result,
        ], 201);
    }

    // GET /owner/usaha/{usaha_id}/cabang/{id}
    public function show(string $usahaId, string $id)
    {
        $tenant = $this->findOwnedTenant($usahaId, $id);

        return response()->json([
            'message' => 'Detail cabang',
            'data'    => $tenant->load('usaha', 'kasirs'),
        ]);
    }

    // PUT /owner/usaha/{usaha_id}/cabang/{id}
    public function update(Request $request, string $usahaId, string $id)
    {
        $request->validate([
            'nama_cabang' => 'sometimes|string|max:100',
            'alamat'      => 'nullable|string',
        ]);

        $tenant = $this->findOwnedTenant($usahaId, $id);
        $tenant = $this->tenantService->update($tenant, $request->all());

        return response()->json([
            'message' => 'Cabang berhasil diupdate',
            'data'    => $tenant,
        ]);
    }

    // PATCH /owner/usaha/{usaha_id}/cabang/{id}/toggle-status
    public function toggleStatus(string $usahaId, string $id)
    {
        $tenant = $this->findOwnedTenant($usahaId, $id);
        $tenant = $this->tenantService->toggleStatus($tenant);

        $status = $tenant->status_buka ? 'dibuka' : 'ditutup';

        return response()->json([
            'message' => "Toko berhasil {$status}",
            'data'    => $tenant,
        ]);
    }

    // DELETE /owner/usaha/{usaha_id}/cabang/{id}
    public function destroy(string $usahaId, string $id)
    {
        $tenant = $this->findOwnedTenant($usahaId, $id);
        $this->tenantService->delete($tenant);

        return response()->json(['message' => 'Cabang dan akun kasir berhasil dihapus']);
    }

    // Helper: pastikan usaha milik owner yang login
    private function authorizeUsaha(string $usahaId): void
    {
        $exists = Usaha::where('id', $usahaId)
                       ->where('owner_id', auth()->id())
                       ->exists();

        if (!$exists) {
            abort(403, 'Usaha ini bukan milik Anda');
        }
    }

    // Helper: cari tenant dan pastikan dalam usaha milik owner yang login
    private function findOwnedTenant(string $usahaId, string $tenantId): Tenant
    {
        $this->authorizeUsaha($usahaId);

        $tenant = Tenant::where('id', $tenantId)
                        ->where('usaha_id', $usahaId)
                        ->first();

        if (!$tenant) {
            abort(404, 'Cabang tidak ditemukan');
        }

        return $tenant;
    }
}