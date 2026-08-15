<?php

use Illuminate\Support\Facades\Route;
use Modules\Identity\Http\Controllers\Api\AdminUserController;

$adminRoles = implode('|', config('permissions.admin_roles', ['admin']));

Route::middleware(['auth:sanctum', "role:{$adminRoles}"])->prefix('admin')->group(function () {
    Route::get('users', [AdminUserController::class, 'index']);
    Route::put('users/{user}/role', [AdminUserController::class, 'updateRole']);
    Route::put('users/{user}/password', [AdminUserController::class, 'updatePassword']);
    Route::delete('users/{user}', [AdminUserController::class, 'destroy']);
});