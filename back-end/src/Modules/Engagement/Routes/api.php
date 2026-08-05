<?php

use Illuminate\Support\Facades\Route;
use Modules\Engagement\Http\Controllers\Api\CommentPublicController;

Route::prefix('v1/posts/{post}/comments')->group(function () {
    Route::get('/', [CommentPublicController::class, 'index']);
});

Route::prefix('v1/comments')->group(function () {
    Route::post('/', [CommentPublicController::class, 'store']);
});