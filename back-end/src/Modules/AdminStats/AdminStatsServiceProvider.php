<?php

namespace Modules\AdminStats;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Modules\AdminStats\Services\Contracts\ContentStatsContract;
use Modules\AdminStats\Services\ContentStatsService;
use Modules\AdminStats\Policies\AdminStatsPolicy;

class AdminStatsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            ContentStatsContract::class,
            ContentStatsService::class
        );
    }

    public function boot(): void
    {
        // Define Gates for model-less authorization (Article X)
        Gate::define('viewAdminDashboard', [AdminStatsPolicy::class, 'viewDashboard']);
        Gate::define('viewAdminAuthors', [AdminStatsPolicy::class, 'viewAuthors']);
    }
}