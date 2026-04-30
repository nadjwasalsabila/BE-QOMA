<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Semua routes dipisah per role untuk kemudahan maintenance.
| Tambahkan prefix sesuai role masing-masing.
|
*/

// Auth (public + protected)
Route::prefix('auth')->group(base_path('routes/auth.php'));

// Super Admin
Route::prefix('super-admin')->group(base_path('routes/superadmin.php'));

// Owner
Route::prefix('owner')->group(base_path('routes/owner.php'));

// Outlet
Route::prefix('outlet')->group(base_path('routes/outlet.php'));