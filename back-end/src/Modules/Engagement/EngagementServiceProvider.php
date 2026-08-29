<?php

namespace Modules\Engagement;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Modules\Engagement\Events\CommentCreated;
use Modules\Engagement\Listeners\SendCommentNotifications;
use Modules\Engagement\Models\Comment;
use Modules\Engagement\Policies\CommentPolicy;
use Modules\Engagement\Services\CommentStatsService;
use Modules\Engagement\Services\Contracts\CommentServiceInterface;

class EngagementServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Read-side contract for cross-module consumers (Articles'
        // MapPostRelationsAction, ReaderExperience's DashboardService).
        // The write side (CommentService) stays concrete and module-internal
        // so it can depend on PostAdminServiceInterface without cycling.
        $this->app->bind(CommentServiceInterface::class, CommentStatsService::class);
    }

    public function boot(): void
    {
        Gate::policy(Comment::class, CommentPolicy::class);
        Event::listen(CommentCreated::class, SendCommentNotifications::class);

        $this->configureRateLimiting();
    }

    /**
     * Public comment creation is the module's only unauthenticated write
     * surface. Two windows per key (burst + sustained) blunt both floods and
     * slow-drip spam. Authenticated commenters are attributable and publish
     * instantly, so they earn a higher ceiling than anonymous guests.
     */
    private function configureRateLimiting(): void
    {
        RateLimiter::for('engagement-comments', function (Request $request) {
            if ($user = $request->user()) {
                return [
                    Limit::perMinute(10)->by('user:' . $user->id),
                    Limit::perHour(60)->by('user:' . $user->id),
                ];
            }

            return [
                Limit::perMinute(5)->by('ip:' . $request->ip()),
                Limit::perHour(25)->by('ip:' . $request->ip()),
            ];
        });
    }
}