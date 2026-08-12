<?php

use Illuminate\Support\Facades\Route;
use Modules\About\Http\Controllers\Api\AboutController;

Route::get('about', [AboutController::class, 'index']);