<?php

namespace Modules\About;

use Illuminate\Support\ServiceProvider;

class AboutServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            \Modules\Identity\Services\Contracts\FetchesUsersByRole::class,
            \Modules\Identity\Services\UserService::class
        );
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/Routes/api.php');
        $this->loadRoutesFrom(__DIR__ . '/Routes/admin.php');
    }
}