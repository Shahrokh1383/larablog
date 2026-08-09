<?php

namespace Modules\ReaderExperience;

use Illuminate\Support\ServiceProvider;
use Modules\ReaderExperience\Services\Contracts\SavedPostInteractionContract;
use Modules\ReaderExperience\Services\SavedPostService;
class ReaderExperienceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(SavedPostInteractionContract::class, SavedPostService::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/Routes/api.php');
    }
}