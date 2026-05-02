<?php

use Illuminate\Support\Facades\Route;

Route::middleware('role:outlet')->group(function () {
    Route::get('dashboard', fn() => response()->json([
        'message' => 'Dashboard Outlet',
        'outlet'  => auth()->user()->load('outlet'),
    ]));
});