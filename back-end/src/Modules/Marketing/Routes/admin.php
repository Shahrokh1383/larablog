<?php

use Illuminate\Support\Facades\Route;
use Modules\Marketing\Http\Controllers\Api\MarketingAdminController;

Route::prefix('admin')->middleware(['auth:sanctum', 'role:admin'])->group(function () {
    
    // Newsletter / Subscribers
    Route::get('/subscribers', [MarketingAdminController::class, 'subscribers']);
    Route::post('/newsletter/send', [MarketingAdminController::class, 'sendNewsletter']);
    Route::delete('/subscribers/{subscriber}', [MarketingAdminController::class, 'deleteSubscriber']);

    // Contact Messages
    Route::get('/contact-messages', [MarketingAdminController::class, 'contactMessages']);
    Route::get('/contact-messages/{message}', [MarketingAdminController::class, 'showContactMessage']);
    Route::delete('/contact-messages/{message}', [MarketingAdminController::class, 'deleteContactMessage']);
});