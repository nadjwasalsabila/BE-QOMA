<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Support\Str;

class ActivityLogService
{
    /**
     * Catat aktivitas.
     *
     * Contoh pemakaian:
     * ActivityLogService::log('approve_usaha', 'Usaha Warung Barokah diapprove', [
     *     'usaha_id' => $usaha->id,
     * ]);
     */
    public static function log(
        string  $aktivitas,
        string  $deskripsi,
        array   $metadata  = [],
        ?string $usahaId   = null,
        ?string $tenantId  = null,
    ): void {
        $user = auth()->user();

        ActivityLog::create([
            'id'         => Str::uuid(),
            'user_id'    => $user?->id,
            'usaha_id'   => $usahaId,
            'tenant_id'  => $tenantId,
            'aktivitas'  => $aktivitas,
            'deskripsi'  => $deskripsi,
            'metadata'   => $metadata ?: null,
            'ip_address' => request()->ip(),
        ]);
    }
}