<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth:api', 'role:outlet'])->group(function () {

    // Dashboard (placeholder, akan diisi di task berikutnya)
    Route::get('dashboard', fn() => response()->json([
        'message' => 'Dashboard Outlet',
        'outlet'  => auth()->user()->load('outlet'),
    ]));

    // akan ditambahkan:
    // - bahan outlet (kelola stok)
    // - pesanan (terima & konfirmasi)
    // - menu outlet (edit harga)
    // - kerugian
    // - laporan keuangan
});