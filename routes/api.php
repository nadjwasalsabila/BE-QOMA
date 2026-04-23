<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Owner\UsahaController;
use App\Http\Controllers\Api\Owner\TenantController;
use App\Http\Controllers\Api\Owner\KasirController;
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
        // tambahkan route owner lainnya di sini
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