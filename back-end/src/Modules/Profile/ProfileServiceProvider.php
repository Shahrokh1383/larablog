<?php

namespace Modules\Profile;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Modules\Identity\Events\UserDeleted;
use Modules\Profile\Listeners\CleanupProfileOnUserDeleted;
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
        // Clean up profile data when a user is deleted by the Identity module.
        Event::listen(UserDeleted::class, CleanupProfileOnUserDeleted::class);
    }
}