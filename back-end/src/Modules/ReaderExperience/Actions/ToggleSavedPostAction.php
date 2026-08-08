<?php

namespace Modules\ReaderExperience\Actions;

use Modules\ReaderExperience\Models\SavedPost;

class ToggleSavedPostAction
{
    /**
     * Toggles the saved state of a post for a user.
     * Returns true if saved, false if unsaved.
     */
    public function execute(string $userId, string $postId): bool
    {
        $savedPost = SavedPost::where('user_id', $userId)
                              ->where('post_id', $postId)
                              ->first();

        if ($savedPost) {
            $savedPost->delete();
            return false; // Unsaved
        }

        SavedPost::create([
            'user_id'  => $userId,
            'post_id'  => $postId,
            'saved_at' => now(),
        ]);

        return true; // Saved
    }
}