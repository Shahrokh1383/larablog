<?php

use Illuminate\Support\Facades\Route;
use Modules\Engagement\Http\Controllers\Api\CommentController;

// Global comment management (admin only)
Route::prefix('admin/comments')->middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::get('/unread-count', [CommentController::class, 'unreadCount']);
    Route::patch('/{comment}/approve', [CommentController::class, 'approve']);
    Route::delete('/{comment}', [CommentController::class, 'destroy']);
});

// Post-scoped comment listing & replying (admin, editor, author access controlled by policy)
Route::middleware('auth:sanctum')->prefix('admin/posts/{post}')->group(function () {
    Route::get('comments', [CommentController::class, 'index']);
    Route::post('comments', [CommentController::class, 'store']);
});