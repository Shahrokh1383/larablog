<?php

use Illuminate\Support\Facades\Route;
use Modules\About\Http\Controllers\Api\AdminSiteSettingsController;
use Modules\About\Http\Controllers\Api\AdminTeamMemberController;

Route::middleware('auth:sanctum')->prefix('admin')->group(function () {
    // Site Settings
    Route::get('about/settings', [AdminSiteSettingsController::class, 'show']);
    Route::put('about/settings', [AdminSiteSettingsController::class, 'update']);

    // Team Members
    Route::get('about/team-members', [AdminTeamMemberController::class, 'index']);
    Route::get('about/eligible-users', [AdminTeamMemberController::class, 'eligibleUsers']);
    Route::post('about/team-members', [AdminTeamMemberController::class, 'store']);
    Route::put('about/team-members/{team_member}', [AdminTeamMemberController::class, 'update']);
    Route::delete('about/team-members/{team_member}', [AdminTeamMemberController::class, 'destroy']);
});