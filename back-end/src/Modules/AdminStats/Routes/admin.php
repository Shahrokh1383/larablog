<?php

use Illuminate\Support\Facades\Route;
use Modules\AdminStats\Http\Controllers\Api\ContentStatsController;

Route::middleware('auth:sanctum')->prefix('admin')->group(function () {
    Route::get('stats/dashboard', [ContentStatsController::class, 'dashboard'])->name('admin.stats.dashboard');
    Route::get('stats/authors', [ContentStatsController::class, 'authors'])->name('admin.stats.authors');
});