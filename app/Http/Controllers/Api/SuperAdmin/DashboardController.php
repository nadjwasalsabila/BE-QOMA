<?php

namespace App\Http\Controllers\Api\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Services\SuperAdmin\DashboardService;

class DashboardController extends Controller
{
    public function __construct(private DashboardService $service) {}

    // GET /super-admin/dashboard
    public function index()
    {
        return response()->json([
            'message' => 'Dashboard Super Admin',
            'data'    => $this->service->getStats(),
        ]);
    }
}