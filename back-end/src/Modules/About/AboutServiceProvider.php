<?php

namespace Modules\About;

use Illuminate\Support\ServiceProvider;

class AboutServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/Routes/api.php');
        $this->loadRoutesFrom(__DIR__ . '/Routes/admin.php');
    }
}