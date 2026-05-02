<?php

use App\Http\Controllers\Api\Owner\BahanMasterController;
use App\Http\Controllers\Api\Owner\KategoriMenuController;
use App\Http\Controllers\Api\Owner\MenuController;
use App\Http\Controllers\Api\Owner\OutletController;
use App\Http\Controllers\Api\Owner\UsahaController;
use Illuminate\Support\Facades\Route;

Route::middleware('role:owner')->group(function () {

    Route::get ('usaha',      [UsahaController::class, 'index']);
    Route::post('usaha',      [UsahaController::class, 'store']);
    Route::get ('usaha/{id}', [UsahaController::class, 'show']);
    Route::put ('usaha/{id}', [UsahaController::class, 'update']);

    Route::get   ('usaha/{usaha_id}/outlet',                    [OutletController::class, 'index']);
    Route::post  ('usaha/{usaha_id}/outlet',                    [OutletController::class, 'store']);
    Route::get   ('usaha/{usaha_id}/outlet/{id}',               [OutletController::class, 'show']);
    Route::put   ('usaha/{usaha_id}/outlet/{id}',               [OutletController::class, 'update']);
    Route::patch ('usaha/{usaha_id}/outlet/{id}/toggle-status', [OutletController::class, 'toggleStatus']);
    Route::delete('usaha/{usaha_id}/outlet/{id}',               [OutletController::class, 'destroy']);

    Route::get   ('usaha/{usaha_id}/bahan-master',      [BahanMasterController::class, 'index']);
    Route::post  ('usaha/{usaha_id}/bahan-master',      [BahanMasterController::class, 'store']);
    Route::get   ('usaha/{usaha_id}/bahan-master/{id}', [BahanMasterController::class, 'show']);
    Route::put   ('usaha/{usaha_id}/bahan-master/{id}', [BahanMasterController::class, 'update']);
    Route::delete('usaha/{usaha_id}/bahan-master/{id}', [BahanMasterController::class, 'destroy']);

    Route::get   ('usaha/{usaha_id}/kategori',      [KategoriMenuController::class, 'index']);
    Route::post  ('usaha/{usaha_id}/kategori',      [KategoriMenuController::class, 'store']);
    Route::get   ('usaha/{usaha_id}/kategori/{id}', [KategoriMenuController::class, 'show']);
    Route::put   ('usaha/{usaha_id}/kategori/{id}', [KategoriMenuController::class, 'update']);
    Route::delete('usaha/{usaha_id}/kategori/{id}', [KategoriMenuController::class, 'destroy']);

    Route::get   ('usaha/{usaha_id}/menu',      [MenuController::class, 'index']);
    Route::post  ('usaha/{usaha_id}/menu',      [MenuController::class, 'store']);
    Route::get   ('usaha/{usaha_id}/menu/{id}', [MenuController::class, 'show']);
    Route::post  ('usaha/{usaha_id}/menu/{id}', [MenuController::class, 'update']);
    Route::delete('usaha/{usaha_id}/menu/{id}', [MenuController::class, 'destroy']);
});