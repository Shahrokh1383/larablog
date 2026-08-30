<?php

use Illuminate\Support\Facades\Route;
use Modules\Authors\Http\Controllers\Api\AuthorDirectoryController;

// Public Routes
Route::get('authors', [AuthorDirectoryController::class, 'index']);
Route::get('authors/{username}', [AuthorDirectoryController::class, 'show']);
Route::get('authors/{username}/posts', [AuthorDirectoryController::class, 'posts']);