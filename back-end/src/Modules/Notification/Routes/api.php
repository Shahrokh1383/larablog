<?php

use Illuminate\Support\Facades\Route;
use Modules\Notification\Http\Controllers\Api\NotificationController;

Route::middleware('auth:sanctum')->prefix('notifications')->group(function () {
    Route::get('/', [NotificationController::class, 'index']);
    Route::post('/mark-as-read', [NotificationController::class, 'markAsRead']);
});