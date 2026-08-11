<?php

namespace Modules\ReaderExperience;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Modules\ReaderExperience\Services\Contracts\SavedPostInteractionContract;
use Modules\ReaderExperience\Services\SavedPostService;
use Modules\Engagement\Events\CommentCreated;
use Modules\ReaderExperience\Listeners\ClearCommentDashboardCacheListener;

class ReaderExperienceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(SavedPostInteractionContract::class, SavedPostService::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/Routes/api.php');
        
        // ReaderExperience listens to Engagement's domain event to clear its own cache
        Event::listen(CommentCreated::class, ClearCommentDashboardCacheListener::class);
    }
}