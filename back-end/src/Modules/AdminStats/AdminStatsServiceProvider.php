<?php

namespace Modules\AdminStats;

use Illuminate\Support\ServiceProvider;
use Modules\AdminStats\Services\Contracts\ContentStatsContract;
use Modules\AdminStats\Services\ContentStatsService;

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
        $this->loadRoutesFrom(__DIR__ . '/Routes/admin.php');
    }
}