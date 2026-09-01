<?php

namespace Modules\Authors;

use Illuminate\Support\ServiceProvider;

class AuthorsServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/Routes/api.php');
    }
}