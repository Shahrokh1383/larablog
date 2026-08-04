<?php

namespace Modules\Profile;

use Illuminate\Support\ServiceProvider;
use Modules\Profile\Services\ProfileService;
use Modules\Profile\Services\Contracts\ProfileServiceInterface;
use Modules\Profile\Services\Contracts\FetchesPublicProfiles;

class ProfileServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ProfileServiceInterface::class, ProfileService::class);
        $this->app->bind(FetchesPublicProfiles::class, ProfileService::class);
    }

    public function boot(): void
    {
        // Routes are strictly loaded via the main routes/api.php file 
        // to ensure they correctly inherit the /api prefix and middleware group.
        // This prevents route duplication and prefix mismatches.
    }
}