<?php

namespace Modules\ReaderExperience;

use Illuminate\Support\ServiceProvider;

class ReaderExperienceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind contracts here in future phases if needed
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/Routes/api.php');
    }
}