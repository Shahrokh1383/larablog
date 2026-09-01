<?php

namespace Modules\ReaderExperience\Listeners;

use Illuminate\Support\Facades\Cache;
use Modules\Engagement\Events\CommentCreated;

class ClearCommentDashboardCacheListener
{
    public function handle(CommentCreated $event): void
    {
        $comment = $event->comment;

        // 1. Invalidate the specific user's dashboard overview (if registered user)
        if ($comment->user_id) {
            Cache::forget("dashboard_overview_{$comment->user_id}");
        }

        // 2. Invalidate the global all-time top commenters cache, the same
        // ranking the admin panel and the reader badge are derived from.
        Cache::forget('all_time_top_commenters');
    }
}