<?php

namespace Modules\Administration;

use Illuminate\Support\ServiceProvider;

class AdministrationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Administration module contains no models or bindings.
        // It only coordinates data from other modules via contracts.
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/Routes/admin.php');
    }
}