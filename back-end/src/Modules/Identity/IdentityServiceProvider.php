<?php

namespace Modules\Identity;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Modules\Identity\Models\User;
use Modules\Identity\Policies\UserPolicy;
use Modules\Identity\Services\Contracts\DeletesUserAccount;
use Modules\Identity\Services\Contracts\FetchesUsersByRole;
use Modules\Identity\Services\Contracts\UpdatesUserBasicInfo;
use Modules\Identity\Services\UserService;

class IdentityServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(UpdatesUserBasicInfo::class, UserService::class);
        $this->app->bind(FetchesUsersByRole::class, UserService::class);
        $this->app->bind(DeletesUserAccount::class, UserService::class);
    }

    public function boot(): void
    {
        // Load views with 'identity' namespace
        $this->loadViewsFrom(__DIR__.'/Resources/views', 'identity');

        // Register policies
        Gate::policy(User::class, UserPolicy::class);
    }
}