<?php

namespace Modules\ReaderExperience\Actions;

use Modules\ReaderExperience\Models\PostRead;
use Illuminate\Support\Carbon;

class TrackPostReadAction
{
    /**
     * Tracks a post read event. 
     * Updates the timestamp if a record already exists (tracks latest read).
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
    }
}