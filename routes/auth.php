<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

// ============================================================
// PUBLIC
// ============================================================
Route::post('login',   [AuthController::class, 'login']);
Route::post('refresh', [AuthController::class, 'refresh']);

// ============================================================
// PROTECTED
// ============================================================
Route::middleware('auth:api')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get ('me',     [AuthController::class, 'me']);
});