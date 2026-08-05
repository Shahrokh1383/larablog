<?php

use Illuminate\Support\Facades\Route;
use Modules\Engagement\Http\Controllers\Api\CommentController;

Route::prefix('admin/comments')->middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::get('/unread-count', [CommentController::class, 'unreadCount']);
    Route::patch('/{comment}/approve', [CommentController::class, 'approve']);
    Route::delete('/{comment}', [CommentController::class, 'destroy']);
});