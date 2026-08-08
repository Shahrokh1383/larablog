<?php

use Illuminate\Support\Facades\Route;
use Modules\Engagement\Http\Controllers\Api\CommentController;
use Modules\Engagement\Http\Controllers\Api\CommentAdminController;

// Existing global comment management (admin only)
Route::prefix('admin/comments')->middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::get('/unread-count', [CommentController::class, 'unreadCount']);
    Route::patch('/{comment}/approve', [CommentController::class, 'approve']);
    Route::delete('/{comment}', [CommentController::class, 'destroy']);
});

// Post-scoped comment listing & replying (admin, editor, author access controlled by policy)
Route::middleware('auth:sanctum')->prefix('admin/posts/{post}')->group(function () {
    Route::get('comments', [CommentAdminController::class, 'index']);
    Route::post('comments', [CommentAdminController::class, 'store']);
});