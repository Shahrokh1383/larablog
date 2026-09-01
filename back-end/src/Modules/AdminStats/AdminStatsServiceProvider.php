<?php

namespace Modules\AdminStats;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Modules\AdminStats\Listeners\ClearTopCommentersCacheListener;
use Modules\AdminStats\Policies\AdminStatsPolicy;
use Modules\AdminStats\Services\Contracts\ContentStatsContract;
use Modules\AdminStats\Services\ContentStatsService;
use Modules\Engagement\Events\CommentCreated;

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
        Gate::define('viewAdminDashboard', [AdminStatsPolicy::class, 'viewDashboard']);
        Gate::define('viewAdminAuthors', [AdminStatsPolicy::class, 'viewAuthors']);
        Gate::define('viewAuthorDashboard', [AdminStatsPolicy::class, 'viewAuthorDashboard']);
        Gate::define('viewAdminTopCommenters', [AdminStatsPolicy::class, 'viewAdminTopCommenters']);
        Event::listen(CommentCreated::class, ClearTopCommentersCacheListener::class);
    }
}