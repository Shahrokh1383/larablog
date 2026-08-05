<?php

namespace Modules\Engagement\Services;

use Modules\Engagement\Models\Comment;
use Illuminate\Pagination\CursorPaginator;

class CommentPublicService
{
    /**
     * Fetch root comments in chunks with their immediate replies.
     */
    public function getCommentsForPost(string $postId, ?string $cursor = null): CursorPaginator
    {
        return Comment::where('post_id', $postId)
            ->approved()
            ->whereNull('parent_id') // Only fetch root comments for pagination
            ->with(['user', 'replies.user']) // Eager load 1 level of replies natively
            ->latest()
            ->cursorPaginate(15, ['*'], 'cursor', $cursor);
    }

    /**
     * Get the total count of approved comments for the UI header.
     */
    public function getTotalCommentsCount(string $postId): int
    {
        return Comment::where('post_id', $postId)->approved()->count();
    }
}