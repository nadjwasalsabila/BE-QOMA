<?php

namespace App\Services\SuperAdmin;

use App\Models\Usaha;
use App\Models\UsahaRejection;
use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UsahaManagementService
{
    // =====================
    // MANAGE USAHA
    // =====================

    /**
     * List semua usaha dengan filter status
     */
    public function listUsaha(array $filters = [], int $perPage = 15)
    {
        $query = Usaha::with(['owner:id,username,nama_lengkap,email,is_active'])
                      ->withCount('tenants');

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('nama_usaha', 'like', "%{$filters['search']}%")
                  ->orWhere('email', 'like', "%{$filters['search']}%");
            });
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Detail 1 usaha lengkap
     */
    public function detailUsaha(string $id): Usaha
    {
        return Usaha::with([
            'owner:id,username,nama_lengkap,email,is_active',
            'tenants:id,usaha_id,nama_cabang,alamat,status_buka',
            'rejections.rejectedBy:id,username',
        ])->findOrFail($id);
    }

    /**
     * Approve usaha (pending → active)
     */
    public function approve(Usaha $usaha): Usaha
    {
        if ($usaha->status !== 'pending') {
            throw new \Exception("Usaha hanya bisa di-approve jika statusnya 'pending'. Status saat ini: {$usaha->status}");
        }

        $usaha->update([
            'status'      => 'active',
            'approved_at' => now(),
        ]);

        // Aktifkan akun owner-nya sekaligus
        if ($usaha->owner_id) {
            User::where('id', $usaha->owner_id)->update(['is_active' => true]);
        }

        ActivityLogService::log(
            'approve_usaha',
            "Usaha '{$usaha->nama_usaha}' berhasil di-approve",
            ['usaha_id' => $usaha->id, 'nama_usaha' => $usaha->nama_usaha],
            $usaha->id,
        );

        return $usaha->fresh('owner');
    }

    /**
     * Reject usaha (pending → rejected)
     */
    public function reject(Usaha $usaha, string $alasan): Usaha
    {
        if ($usaha->status !== 'pending') {
            throw new \Exception("Usaha hanya bisa di-reject jika statusnya 'pending'.");
        }

        $usaha->update([
            'status'      => 'rejected',
            'rejected_at' => now(),
            'catatan_admin' => $alasan,
        ]);

        // Simpan ke history rejection
        UsahaRejection::create([
            'id'          => Str::uuid(),
            'usaha_id'    => $usaha->id,
            'rejected_by' => auth()->id(),
            'alasan'      => $alasan,
        ]);

        // Nonaktifkan owner-nya
        if ($usaha->owner_id) {
            User::where('id', $usaha->owner_id)->update(['is_active' => false]);
        }

        ActivityLogService::log(
            'reject_usaha',
            "Usaha '{$usaha->nama_usaha}' ditolak. Alasan: {$alasan}",
            ['usaha_id' => $usaha->id, 'alasan' => $alasan],
            $usaha->id,
        );

        return $usaha->fresh('owner', 'rejections');
    }

    /**
     * Suspend usaha (active → suspended)
     */
    public function suspend(Usaha $usaha, ?string $catatan = null): Usaha
    {
        if ($usaha->status === 'suspended') {
            throw new \Exception('Usaha sudah dalam status suspended.');
        }

        $usaha->update([
            'status'        => 'suspended',
            'catatan_admin' => $catatan,
        ]);

        // Nonaktifkan owner + semua kasir di usaha ini
        $this->toggleUsahaUsers($usaha, false);

        ActivityLogService::log(
            'suspend_usaha',
            "Usaha '{$usaha->nama_usaha}' disuspend. Catatan: {$catatan}",
            ['usaha_id' => $usaha->id],
            $usaha->id,
        );

        return $usaha->fresh();
    }

    /**
     * Unsuspend usaha (suspended → active)
     */
    public function unsuspend(Usaha $usaha): Usaha
    {
        if ($usaha->status !== 'suspended') {
            throw new \Exception('Usaha tidak dalam status suspended.');
        }

        $usaha->update(['status' => 'active', 'catatan_admin' => null]);

        // Aktifkan kembali owner + semua kasir
        $this->toggleUsahaUsers($usaha, true);

        ActivityLogService::log(
            'unsuspend_usaha',
            "Usaha '{$usaha->nama_usaha}' diaktifkan kembali",
            ['usaha_id' => $usaha->id],
            $usaha->id,
        );

        return $usaha->fresh();
    }

    // =====================
    // MANAGE OWNER
    // =====================

    /**
     * List semua owner
     */
    public function listOwner(array $filters = [], int $perPage = 15)
    {
        $query = User::whereHas('role', fn($q) => $q->where('name', 'owner'))
                     ->with(['usaha:id,owner_id,nama_usaha,status'])
                     ->select('id', 'username', 'nama_lengkap', 'email', 'is_active', 'usaha_id', 'created_at');

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('username', 'like', "%{$filters['search']}%")
                  ->orWhere('email', 'like', "%{$filters['search']}%")
                  ->orWhere('nama_lengkap', 'like', "%{$filters['search']}%");
            });
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * List owner per usaha tertentu
     */
    public function ownerByUsaha(string $usahaId)
    {
        return User::whereHas('role', fn($q) => $q->where('name', 'owner'))
                   ->where('usaha_id', $usahaId)
                   ->with('usaha:id,nama_usaha,status')
                   ->first();
    }

    /**
     * Reset password owner
     */
    public function resetPasswordOwner(User $owner): string
    {
        $newPassword = Str::random(12);
        $owner->update(['password' => Hash::make($newPassword)]);

        ActivityLogService::log(
            'reset_password_owner',
            "Password owner '{$owner->username}' direset oleh super admin",
            ['owner_id' => $owner->id, 'username' => $owner->username],
            $owner->usaha_id,
        );

        return $newPassword;
    }

    /**
     * Nonaktifkan / aktifkan akun owner
     */
    public function toggleOwnerStatus(User $owner): User
    {
        $owner->update(['is_active' => !$owner->is_active]);

        $status = $owner->is_active ? 'diaktifkan' : 'dinonaktifkan';

        ActivityLogService::log(
            'toggle_owner_status',
            "Akun owner '{$owner->username}' {$status}",
            ['owner_id' => $owner->id, 'is_active' => $owner->is_active],
        );

        return $owner->fresh();
    }

    // =====================
    // PRIVATE HELPERS
    // =====================

    /**
     * Aktifkan / nonaktifkan semua user dalam 1 usaha
     * (owner + semua kasir + admin cabang)
     */
    private function toggleUsahaUsers(Usaha $usaha, bool $isActive): void
    {
        // Toggle owner
        if ($usaha->owner_id) {
            User::where('id', $usaha->owner_id)->update(['is_active' => $isActive]);
        }

        // Toggle semua user yang punya usaha_id ini (kasir, admin cabang, dll)
        User::where('usaha_id', $usaha->id)
            ->where('id', '!=', $usaha->owner_id)
            ->update(['is_active' => $isActive]);
    }
}