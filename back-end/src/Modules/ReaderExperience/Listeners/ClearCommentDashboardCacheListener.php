<?php

namespace Modules\ReaderExperience\Listeners;

use Illuminate\Support\Facades\Cache;
use Modules\Engagement\Events\CommentCreated;

class ClearCommentDashboardCacheListener
{
    /**
     * Handle the event to clear dashboard caches when a new comment is created.
     * This respects Article IV (Async Cross-Module Communication).
     */
    public function handle(CommentCreated $event): void
    {
        $comment = $event->comment;
        
        // 1. Invalidate the specific user's dashboard overview (if registered user)
        if ($comment->user_id) {
            Cache::forget("dashboard_overview_{$comment->user_id}");
        }

        // 2. Invalidate the global top commenters cache since a new comment was added
        Cache::forget('weekly_top_commenters');
    }
}