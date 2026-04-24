<?php

namespace App\Http\Controllers\Api\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Traits\HasPagination;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    use HasPagination;

    // GET /super-admin/activity-logs?usaha_id=xxx&aktivitas=approve_usaha&page=1
    public function index(Request $request)
    {
        $query = ActivityLog::with('user:id,username')
                            ->latest();

        if ($request->usaha_id) {
            $query->where('usaha_id', $request->usaha_id);
        }

        if ($request->tenant_id) {
            $query->where('tenant_id', $request->tenant_id);
        }

        if ($request->aktivitas) {
            $query->where('aktivitas', $request->aktivitas);
        }

        if ($request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        // Filter tanggal
        if ($request->dari) {
            $query->whereDate('created_at', '>=', $request->dari);
        }
        if ($request->sampai) {
            $query->whereDate('created_at', '<=', $request->sampai);
        }

        $logs = $query->paginate($this->getPerPage($request));

        return response()->json(
            $this->paginateResponse($logs, 'Activity logs')
        );
    }
}