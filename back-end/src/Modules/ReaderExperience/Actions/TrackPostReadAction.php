<?php

namespace Modules\ReaderExperience\Actions;

use Modules\ReaderExperience\Models\PostRead;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class TrackPostReadAction
{
    /**
     * Tracks a post read event and invalidates the user's dashboard cache.
     */
    public function execute(string $userId, string $postId): void
    {
        PostRead::updateOrCreate(
            [
                'user_id' => $userId,
                'post_id' => $postId,
            ],
            [
                'read_at' => Carbon::now(),
            ]
        );

        // Invalidate dashboard cache so the next frontend refetch gets fresh data instantly
        Cache::forget("dashboard_overview_{$userId}");
    }
}