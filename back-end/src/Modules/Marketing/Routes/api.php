<?php

use Illuminate\Support\Facades\Route;
use Modules\Marketing\Http\Controllers\Api\NewsletterController;
use Modules\Marketing\Http\Controllers\Api\ContactController;

Route::prefix('newsletter')->group(function () {
    Route::post('/subscribe', [NewsletterController::class, 'subscribe']);
});

Route::prefix('contact')->group(function () {
    Route::post('/', [ContactController::class, 'store']);
});