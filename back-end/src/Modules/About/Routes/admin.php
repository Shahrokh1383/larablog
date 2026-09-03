<?php

use Illuminate\Support\Facades\Route;
use Modules\About\Http\Controllers\Api\AdminSiteSettingsController;
use Modules\About\Http\Controllers\Api\AdminTeamMemberController;
use Modules\About\Models\SiteSetting;
use Modules\About\Models\TeamMember;

Route::middleware('auth:sanctum')->prefix('admin')->group(function () {
    // Site Settings
    Route::get('about/settings', [AdminSiteSettingsController::class, 'show'])
        ->middleware('can:view,' . SiteSetting::class);

    Route::put('about/settings', [AdminSiteSettingsController::class, 'update'])
        ->middleware('can:update,' . SiteSetting::class);

    Route::post('about/upload-story-image', [AdminSiteSettingsController::class, 'uploadStoryImage'])
        ->middleware('can:update,' . SiteSetting::class);

    Route::delete('about/delete-story-image', [AdminSiteSettingsController::class, 'deleteStoryImage'])
        ->middleware('can:update,' . SiteSetting::class);

    // Team Members
    Route::get('about/team-members', [AdminTeamMemberController::class, 'index'])
        ->middleware('can:viewAny,' . TeamMember::class);

    Route::get('about/eligible-users', [AdminTeamMemberController::class, 'eligibleUsers'])
        ->middleware('can:viewAny,' . TeamMember::class);

    Route::post('about/team-members', [AdminTeamMemberController::class, 'store'])
        ->middleware('can:create,' . TeamMember::class);

    Route::put('about/team-members/{team_member}', [AdminTeamMemberController::class, 'update'])
        ->middleware('can:update,team_member');

    Route::delete('about/team-members/{team_member}', [AdminTeamMemberController::class, 'destroy'])
        ->middleware('can:delete,team_member');
});