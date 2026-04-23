<?php

namespace App\Http\Controllers\Api\Owner;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\Usaha;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
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

class KasirController extends Controller
{
    // GET /owner/usaha/{usaha_id}/cabang/{tenant_id}/kasir
    public function index(string $usahaId, string $tenantId)
    {
        $this->authorizeTenant($usahaId, $tenantId);

        $kasirs = User::where('tenant_id', $tenantId)
                      ->whereHas('role', fn($q) => $q->where('name', 'kasir'))
                      ->get(['id', 'username', 'email', 'created_at']);

        return response()->json([
            'message' => 'Daftar kasir',
            'data'    => $kasirs,
        ]);
    }

    // POST /owner/usaha/{usaha_id}/cabang/{tenant_id}/kasir — tambah kasir manual
    public function store(Request $request, string $usahaId, string $tenantId)
    {
        $this->authorizeTenant($usahaId, $tenantId);

        $request->validate([
            'username' => 'required|string|unique:users,username',
            'email'    => 'nullable|email',
        ]);

        $kasirRole     = Role::where('name', 'kasir')->firstOrFail();
        $plainPassword = Str::random(10);

        $kasir = User::create([
            'id'        => Str::uuid(),
            'role_id'   => $kasirRole->id,
            'tenant_id' => $tenantId,
            'usaha_id'  => $usahaId,
            'username'  => $request->username,
            'password'  => Hash::make($plainPassword),
            'email'     => $request->email,
        ]);

        return response()->json([
            'message' => 'Kasir berhasil ditambahkan',
            'data'    => [
                'id'       => $kasir->id,
                'username' => $kasir->username,
                'password' => $plainPassword, // ⚠️ Hanya tampil sekali
                'note'     => 'Simpan password ini! Tidak bisa ditampilkan lagi.',
            ],
        ], 201);
    }

    // PUT /owner/usaha/{usaha_id}/cabang/{tenant_id}/kasir/{kasir_id} — reset password
    public function resetPassword(string $usahaId, string $tenantId, string $kasirId)
    {
        $this->authorizeTenant($usahaId, $tenantId);

        $kasir = User::where('id', $kasirId)
                     ->where('tenant_id', $tenantId)
                     ->firstOrFail();

        $newPassword = Str::random(10);
        $kasir->update(['password' => Hash::make($newPassword)]);

        return response()->json([
            'message'      => 'Password kasir berhasil direset',
            'new_password' => $newPassword, // ⚠️ Hanya tampil sekali
            'note'         => 'Simpan password ini! Tidak bisa ditampilkan lagi.',
        ]);
    }

    // DELETE /owner/usaha/{usaha_id}/cabang/{tenant_id}/kasir/{kasir_id}
    public function destroy(string $usahaId, string $tenantId, string $kasirId)
    {
        $this->authorizeTenant($usahaId, $tenantId);

        $kasir = User::where('id', $kasirId)
                     ->where('tenant_id', $tenantId)
                     ->firstOrFail();

        $kasir->delete();

        return response()->json(['message' => 'Akun kasir berhasil dihapus']);
    }

    // Helper: pastikan tenant ada dan milik usaha si owner
    private function authorizeTenant(string $usahaId, string $tenantId): void
    {
        $ownerPunya = Usaha::where('id', $usahaId)
                           ->where('owner_id', auth()->id())
                           ->exists();

        if (!$ownerPunya) abort(403, 'Usaha bukan milik Anda');

        $tenantAda = Tenant::where('id', $tenantId)
                           ->where('usaha_id', $usahaId)
                           ->exists();

        if (!$tenantAda) abort(404, 'Cabang tidak ditemukan');
    }
}