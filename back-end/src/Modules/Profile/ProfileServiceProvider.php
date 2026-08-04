<?php

namespace Modules\Profile;

use Illuminate\Support\ServiceProvider;
use Modules\Profile\Services\ProfileService;
use Modules\Profile\Services\Contracts\ProfileServiceInterface;

class ProfileServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ProfileServiceInterface::class, ProfileService::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/Routes/api.php');
    }
}