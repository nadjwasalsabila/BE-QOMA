<?php

use App\Http\Controllers\Api\SuperAdmin\ActivityLogController;
use App\Http\Controllers\Api\SuperAdmin\DashboardController;
use App\Http\Controllers\Api\SuperAdmin\NotificationController;
use App\Http\Controllers\Api\SuperAdmin\OwnerManagementController;
use App\Http\Controllers\Api\SuperAdmin\PlanController;
use App\Http\Controllers\Api\SuperAdmin\SubscriptionController;
use App\Http\Controllers\Api\SuperAdmin\UsahaManagementController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:api', 'role:super_admin'])->group(function () {

    // Dashboard
    Route::get('dashboard', [DashboardController::class, 'index']);

    // Activity Logs
    Route::get('activity-logs', [ActivityLogController::class, 'index']);

    // Notifikasi
    Route::get  ('notifications',           [NotificationController::class, 'index']);
    Route::patch('notifications/{id}/read', [NotificationController::class, 'markRead']);
    Route::patch('notifications/read-all',  [NotificationController::class, 'markAllRead']);

    // -------------------------------------------------------
    // Manage Usaha
    // -------------------------------------------------------
    Route::get ('usaha',                [UsahaManagementController::class, 'index']);
    Route::get ('usaha/pending',        [UsahaManagementController::class, 'pending']);
    Route::get ('usaha/{id}',           [UsahaManagementController::class, 'show']);
    Route::post('usaha/{id}/approve',   [UsahaManagementController::class, 'approve']);
    Route::post('usaha/{id}/reject',    [UsahaManagementController::class, 'reject']);
    Route::post('usaha/{id}/suspend',   [UsahaManagementController::class, 'suspend']);
    Route::post('usaha/{id}/unsuspend', [UsahaManagementController::class, 'unsuspend']);

    // -------------------------------------------------------
    // Manage Owner
    // -------------------------------------------------------
    Route::get  ('owner',                    [OwnerManagementController::class, 'index']);
    Route::get  ('usaha/{usaha_id}/owner',   [OwnerManagementController::class, 'byUsaha']);
    Route::post ('owner/{id}/reset-password',[OwnerManagementController::class, 'resetPassword']);
    Route::patch('owner/{id}/toggle-status', [OwnerManagementController::class, 'toggleStatus']);

    // -------------------------------------------------------
    // Plans
    // -------------------------------------------------------
    Route::get   ('plans',      [PlanController::class, 'index']);
    Route::post  ('plans',      [PlanController::class, 'store']);
    Route::get   ('plans/{id}', [PlanController::class, 'show']);
    Route::put   ('plans/{id}', [PlanController::class, 'update']);
    Route::delete('plans/{id}', [PlanController::class, 'destroy']);

    // -------------------------------------------------------
    // Subscriptions
    // -------------------------------------------------------
    Route::get('subscriptions',      [SubscriptionController::class, 'index']);
    Route::get('subscriptions/{id}', [SubscriptionController::class, 'show']);
});