<?php

use Illuminate\Support\Facades\Route;
use Modules\Engagement\Http\Controllers\Api\CommentPublicController;

Route::prefix('posts/{post}/comments')->group(function () {
    Route::get('/', [CommentPublicController::class, 'index']);
});

Route::prefix('comments')->group(function () {
    Route::post('/', [CommentPublicController::class, 'store']);
    Route::get('/{comment}/replies', [CommentPublicController::class, 'replies']);
});