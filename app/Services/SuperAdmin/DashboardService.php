<?php

namespace App\Services\SuperAdmin;

use App\Models\ActivityLog;
use App\Models\Tenant;
use App\Models\Usaha;
use App\Models\User;

class DashboardService
{
    public function getStats(): array
    {
        return [
            'usaha' => [
                'total'     => Usaha::count(),
                'pending'   => Usaha::where('status', 'pending')->count(),
                'active'    => Usaha::where('status', 'active')->count(),
                'suspended' => Usaha::where('status', 'suspended')->count(),
                'rejected'  => Usaha::where('status', 'rejected')->count(),
            ],
            'users' => [
                'total'   => User::count(),
                'active'  => User::where('is_active', true)->count(),
                'owner'   => User::whereHas('role', fn($q) => $q->where('name', 'owner'))->count(),
                'kasir'   => User::whereHas('role', fn($q) => $q->where('name', 'kasir'))->count(),
            ],
            'cabang' => [
                'total'  => Tenant::count(),
                'buka'   => Tenant::where('status_buka', true)->count(),
                'tutup'  => Tenant::where('status_buka', false)->count(),
            ],
            'pending_approvals' => Usaha::where('status', 'pending')
                ->with('owner:id,username,email')
                ->latest()
                ->limit(5)
                ->get(['id', 'nama_usaha', 'email', 'owner_id', 'created_at']),

            'recent_activities' => ActivityLog::with('user:id,username')
                ->latest()
                ->limit(10)
                ->get(),
        ];
    }
}