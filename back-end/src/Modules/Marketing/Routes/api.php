<?php

use Illuminate\Support\Facades\Route;
use Modules\Marketing\Http\Controllers\Api\NewsletterController;
use Modules\Marketing\Http\Controllers\Api\ContactController;

Route::prefix('newsletter')->middleware('throttle:10,1')->group(function () {
    Route::post('/subscribe', [NewsletterController::class, 'subscribe']);
});

Route::prefix('contact')->middleware('throttle:5,1')->group(function () {
    Route::post('/', [ContactController::class, 'store']);
});