<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Owner\UsahaController;
use App\Http\Controllers\Api\Owner\TenantController;
use App\Http\Controllers\Api\Owner\KasirController;
use App\Http\Controllers\Api\Owner\KategoriMenuController;
use App\Http\Controllers\Api\Owner\MenuController;
use App\Http\Controllers\Api\SuperAdmin\ActivityLogController;
use App\Http\Controllers\Api\SuperAdmin\DashboardController;
use App\Http\Controllers\Api\SuperAdmin\OwnerManagementController;
use App\Http\Controllers\Api\SuperAdmin\UsahaManagementController;
use Illuminate\Support\Facades\Route;

// AUTH ROUTES (Public)
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login',    [AuthController::class, 'login']);
    Route::post('/refresh',  [AuthController::class, 'refresh']);
});

// PROTECTED ROUTES
Route::middleware('auth:api')->group(function () {

    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me',      [AuthController::class, 'me']);

    // SUPER ADMIN only
    Route::middleware('role:super_admin')->prefix('super-admin')->group(function () {
        Route::get('/dashboard', fn() => response()->json(['message' => 'Selamat datang Super Admin']));
        // tambahkan route super admin lainnya di sini
    });

    // OWNER only
    Route::middleware('role:owner')->prefix('owner')->group(function () {
        Route::get('/dashboard', fn() => response()->json(['message' => 'Selamat datang Owner']));
        
        // --- KATEGORI MENU ---
        Route::get   ('usaha/{usaha_id}/kategori',      [KategoriMenuController::class, 'index']);
        Route::post  ('usaha/{usaha_id}/kategori',      [KategoriMenuController::class, 'store']);
        Route::put   ('usaha/{usaha_id}/kategori/{id}', [KategoriMenuController::class, 'update']);
        Route::delete('usaha/{usaha_id}/kategori/{id}', [KategoriMenuController::class, 'destroy']);

        // --- MENU ---
        Route::get   ('usaha/{usaha_id}/menu',      [MenuController::class, 'index']);
        Route::post  ('usaha/{usaha_id}/menu',      [MenuController::class, 'store']);
        Route::get   ('usaha/{usaha_id}/menu/{id}', [MenuController::class, 'show']);
        // ⚠️ Pakai POST + ?_method=PUT untuk update dengan gambar (multipart/form-data)
        Route::post  ('usaha/{usaha_id}/menu/{id}', [MenuController::class, 'update']);
        Route::delete('usaha/{usaha_id}/menu/{id}', [MenuController::class, 'destroy']);
    });

    // ADMIN CABANG only
    Route::middleware('role:admin_cabang')->prefix('admin-cabang')->group(function () {
        Route::get('/dashboard', fn() => response()->json(['message' => 'Selamat datang Admin Cabang']));
        // tambahkan route admin cabang lainnya di sini
    });

    // KASIR only
    Route::middleware('role:kasir')->prefix('kasir')->group(function () {
        Route::get('/dashboard', fn() => response()->json(['message' => 'Selamat datang Kasir']));
        // tambahkan route kasir lainnya di sini
    });

    // OWNER + SUPER ADMIN (multi role)
    Route::middleware('role:owner,super_admin')->prefix('management')->group(function () {
        Route::get('/users', fn() => response()->json(['message' => 'List users - owner & super admin bisa akses']));
    });
});

// OWNER ROUTES
Route::middleware(['auth:api', 'role:owner'])->prefix('owner')->group(function () {

    // USAHA
    Route::get   ('usaha',          [UsahaController::class, 'index']);
    Route::post  ('usaha',          [UsahaController::class, 'store']);
    Route::get   ('usaha/{id}',     [UsahaController::class, 'show']);
    Route::put   ('usaha/{id}',     [UsahaController::class, 'update']);
    Route::delete('usaha/{id}',     [UsahaController::class, 'destroy']);

    // CABANG (nested di bawah usaha)
    Route::get   ('usaha/{usaha_id}/cabang',                      [TenantController::class, 'index']);
    Route::post  ('usaha/{usaha_id}/cabang',                      [TenantController::class, 'store']);
    Route::get   ('usaha/{usaha_id}/cabang/{id}',                 [TenantController::class, 'show']);
    Route::put   ('usaha/{usaha_id}/cabang/{id}',                 [TenantController::class, 'update']);
    Route::patch ('usaha/{usaha_id}/cabang/{id}/toggle-status',   [TenantController::class, 'toggleStatus']);
    Route::delete('usaha/{usaha_id}/cabang/{id}',                 [TenantController::class, 'destroy']);

    // KASIR (nested di bawah cabang)
    Route::get   ('usaha/{usaha_id}/cabang/{tenant_id}/kasir',                            [KasirController::class, 'index']);
    Route::post  ('usaha/{usaha_id}/cabang/{tenant_id}/kasir',                            [KasirController::class, 'store']);
    Route::put   ('usaha/{usaha_id}/cabang/{tenant_id}/kasir/{kasir_id}/reset-password',  [KasirController::class, 'resetPassword']);
    Route::delete('usaha/{usaha_id}/cabang/{tenant_id}/kasir/{kasir_id}',                 [KasirController::class, 'destroy']);
});

// SUPER ADMIN ROUTES
Route::middleware(['auth:api', 'role:super_admin'])->prefix('super-admin')->group(function () {

    // Dashboard & statistik
    Route::get('dashboard', [DashboardController::class, 'index']);

    // Activity Logs
    Route::get('activity-logs', [ActivityLogController::class, 'index']);

    // Manage Usaha
    Route::get ('usaha',                 [UsahaManagementController::class, 'index']);
    Route::get ('usaha/pending',         [UsahaManagementController::class, 'pending']);
    Route::get ('usaha/{id}',            [UsahaManagementController::class, 'show']);
    Route::post('usaha/{id}/approve',    [UsahaManagementController::class, 'approve']);
    Route::post('usaha/{id}/reject',     [UsahaManagementController::class, 'reject']);
    Route::post('usaha/{id}/suspend',    [UsahaManagementController::class, 'suspend']);
    Route::post('usaha/{id}/unsuspend',  [UsahaManagementController::class, 'unsuspend']);

    // Manage Owner
    Route::get  ('owner',                        [OwnerManagementController::class, 'index']);
    Route::get  ('usaha/{usaha_id}/owner',        [OwnerManagementController::class, 'byUsaha']);
    Route::post ('owner/{id}/reset-password',     [OwnerManagementController::class, 'resetPassword']);
    Route::patch('owner/{id}/toggle-status',      [OwnerManagementController::class, 'toggleStatus']);
});