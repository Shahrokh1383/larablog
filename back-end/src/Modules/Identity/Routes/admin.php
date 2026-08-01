<?php

use Illuminate\Support\Facades\Route;
use Modules\Identity\Http\Controllers\Api\AdminUserController;

Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('users', [AdminUserController::class, 'index']);
    Route::put('users/{user}/role', [AdminUserController::class, 'updateRole']);
    Route::put('users/{user}/password', [AdminUserController::class, 'updatePassword']);
});