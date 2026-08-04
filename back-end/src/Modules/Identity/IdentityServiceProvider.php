<?php

namespace Modules\Identity;

use Illuminate\Support\ServiceProvider;
use Modules\Identity\Services\Contracts\UpdatesUserBasicInfo;
use Modules\Identity\Services\UserService;
use Illuminate\Support\Facades\Gate;
use Modules\Identity\Models\User;
use Modules\Identity\Policies\UserPolicy;

class IdentityServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(UpdatesUserBasicInfo::class, UserService::class);
    }

    public function boot(): void
    {
        // Load views with 'identity' namespace
        $this->loadViewsFrom(__DIR__.'/Resources/views', 'identity');

        // Register policies
        Gate::policy(User::class, UserPolicy::class);
    }
}