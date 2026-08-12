<?php

use Illuminate\Support\Facades\Route;
use Modules\Search\Http\Controllers\Api\SearchController;

Route::get('/', [SearchController::class, 'index']);