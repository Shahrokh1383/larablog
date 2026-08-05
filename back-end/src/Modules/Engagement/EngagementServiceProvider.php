<?php

namespace Modules\Engagement;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Event;
use Modules\Engagement\Models\Comment;
use Modules\Engagement\Policies\CommentPolicy;
use Modules\Engagement\Services\CommentService;
use Modules\Engagement\Services\Contracts\CommentServiceInterface;
use Modules\Engagement\Events\CommentCreated;
use Modules\Engagement\Listeners\SendCommentNotifications;

class EngagementServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(CommentServiceInterface::class, CommentService::class);
    }

    public function boot(): void
    {
        Gate::policy(Comment::class, CommentPolicy::class);
        Event::listen(CommentCreated::class, SendCommentNotifications::class);
    }
}