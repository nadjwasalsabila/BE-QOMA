<?php

namespace App\Services;

use App\Models\Usaha;
use App\Models\User;
use Illuminate\Support\Str;

class UsahaService
{
    /**
     * Buat usaha baru untuk owner tertentu
     */
    public function create(array $data, string $ownerId): Usaha
    {
        return Usaha::create([
            'id'         => Str::uuid(),
            'nama_usaha' => $data['nama_usaha'],
            'owner_id'   => $ownerId,
            'email'      => $data['email'] ?? null,
        ]);
    }

    /**
     * Ambil semua usaha milik owner ini
     */
    public function getByOwner(string $ownerId)
    {
        return Usaha::where('owner_id', $ownerId)
                    ->withCount('tenants')
                    ->get();
    }

    /**
     * Update usaha — pastikan hanya owner-nya yang bisa
     */
    public function update(Usaha $usaha, array $data): Usaha
    {
        $usaha->update([
            'nama_usaha' => $data['nama_usaha'] ?? $usaha->nama_usaha,
            'email'      => $data['email'] ?? $usaha->email,
        ]);

        return $usaha->fresh();
    }

    /**
     * Hapus usaha beserta semua cabang dan kasirnya (cascade)
     */
    public function delete(Usaha $usaha): void
    {
        // Hapus semua user kasir yang terkait tenant di usaha ini
        $tenantIds = $usaha->tenants()->pluck('id');
        User::whereIn('tenant_id', $tenantIds)->delete();

        $usaha->delete(); // cascade akan hapus tenant juga
    }
}