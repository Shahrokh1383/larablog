<?php

use Illuminate\Support\Facades\Route;
use Modules\Home\Http\Controllers\Api\HomePublicController;

Route::get('/home', [HomePublicController::class, 'index']);