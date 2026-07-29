<?php

use Illuminate\Support\Facades\Route;
use Modules\Identity\Http\Controllers\Api\AuthController;
use Modules\Identity\Http\Controllers\Api\OAuthController;
use Modules\Identity\Http\Controllers\Api\AuthorController;

// Public authentication
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

// Email verification (signed URL)
Route::get('email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
    ->middleware('signed')
    ->name('verification.verify');

// OAuth
Route::get('oauth/{provider}/redirect', [OAuthController::class, 'redirect']);
Route::get('oauth/{provider}/callback', [OAuthController::class, 'callback']);

// Public author profiles
Route::get('authors', [AuthorController::class, 'index']);
Route::get('authors/{username}', [AuthorController::class, 'show']);

// Authenticated routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    // Additional authenticated identity routes can go here
});