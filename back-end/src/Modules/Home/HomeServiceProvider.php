<?php

namespace Modules\Home;

use Illuminate\Support\ServiceProvider;
use Modules\Home\Services\HomeStatsService;
use Modules\Home\Services\Contracts\HomeStatsContract;

class HomeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(HomeStatsContract::class, HomeStatsService::class);
    }

    public function boot(): void
    {
        //
    }
}