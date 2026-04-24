<?php

namespace App\Services;

use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Services\MenuService;

class TenantService
{

    public function __construct(private MenuService $menuService) {}

    /**
     * Buat cabang baru + auto generate akun kasir
     *
     * Flow:
     * 1. Buat record tenant
     * 2. Auto buat akun kasir untuk cabang tersebut
     * 3. Return tenant + info akun kasir yang baru dibuat
     */
    public function create(array $data, string $usahaId): array
    {
        // 1. Buat tenant/cabang
        $tenant = Tenant::create([
            'id'          => Str::uuid(),
            'usaha_id'    => $usahaId,
            'nama_cabang' => $data['nama_cabang'],
            'alamat'      => $data['alamat'] ?? null,
            'status_buka' => true,
        ]);

        // 2. Auto generate akun kasir
        //    Username: kasir_<slug nama cabang>_<4 random char>
        //    Password: di-return sekali ke owner, setelah itu tidak bisa dilihat lagi
        $kasirRole    = Role::where('name', 'kasir')->first();
        $randomSuffix = strtolower(Str::random(4));
        $slugCabang   = strtolower(Str::slug($data['nama_cabang'], '_'));
        $username     = "kasir_{$slugCabang}_{$randomSuffix}";
        $plainPassword = Str::random(10); // password plain, hanya ditampilkan sekali

        $kasir = User::create([
            'id'        => Str::uuid(),
            'role_id'   => $kasirRole->id,
            'tenant_id' => $tenant->id,
            'usaha_id'  => $usahaId,
            'username'  => $username,
            'password'  => Hash::make($plainPassword),
            'email'     => $data['email_kasir'] ?? null,
            
        ]);
    
    $this->menuService->syncMenuTenantUntukCabangBaru($tenant->id, $usahaId);

        return [
            'tenant' => $tenant->load('usaha'),
            'kasir'  => [
                'id'       => $kasir->id,
                'username' => $kasir->username,
                'password' => $plainPassword, // ⚠️ HANYA ditampilkan sekali ini
                'note'     => 'Simpan password ini! Tidak bisa ditampilkan lagi.',
            ],
        ];
    }

    /**
     * Ambil semua cabang milik usaha tertentu
     */
    public function getByUsaha(string $usahaId)
    {
        return Tenant::where('usaha_id', $usahaId)
                     ->with('kasirs')
                     ->get();
    }

    /**
     * Update data cabang
     */
    public function update(Tenant $tenant, array $data): Tenant
    {
        $tenant->update([
            'nama_cabang' => $data['nama_cabang'] ?? $tenant->nama_cabang,
            'alamat'      => $data['alamat'] ?? $tenant->alamat,
        ]);

        return $tenant->fresh('usaha', 'kasirs');
    }

    /**
     * Toggle buka/tutup toko
     */
    public function toggleStatus(Tenant $tenant): Tenant
    {
        $tenant->update(['status_buka' => !$tenant->status_buka]);
        return $tenant->fresh();
    }

    /**
     * Hapus cabang + semua kasirnya
     */
    public function delete(Tenant $tenant): void
    {
        // Hapus semua akun kasir di cabang ini dulu
        User::where('tenant_id', $tenant->id)->delete();
        $tenant->delete();
    }
    
}