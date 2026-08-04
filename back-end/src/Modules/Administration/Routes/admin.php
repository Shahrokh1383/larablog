<?php

use Illuminate\Support\Facades\Route;
use Modules\Administration\Http\Controllers\Api\DashboardController;

Route::middleware('auth:sanctum')->prefix('admin')->group(function () {
    Route::get('dashboard/stats', [DashboardController::class, 'index']);
});