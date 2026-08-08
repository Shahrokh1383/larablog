<?php

namespace Modules\Engagement\Services;

use Modules\Engagement\Models\Comment;
use Illuminate\Pagination\CursorPaginator;

class CommentPublicService
{
    /**
     * Fetch root comments with a preview of replies (max 2).
     */
    public function getCommentsForPost(string $postId, ?string $cursor = null): CursorPaginator
    {
        return Comment::where('post_id', $postId)
            ->approved()
            ->whereNull('parent_id')
            ->with([
                'user',
                'replies' => fn($query) => $query->with('user')->latest()->limit(2), // Preview 2 replies
            ])
            ->withCount(['replies as replies_count' => fn($query) => $query->approved()])
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

    /**
     * Fetch paginated replies for a specific comment.
     */
    public function getRepliesForComment(string $commentId, int $skip = 2, int $take = 10): array
    {
        $replies = Comment::where('parent_id', $commentId)
            ->approved()
            ->with('user')
            ->latest()
            ->skip($skip)
            ->take($take + 1) // Take 1 extra to determine if there are more
            ->get();

        $hasMore = $replies->count() > $take;
        $data = $hasMore ? $replies->slice(0, $take) : $replies;

        return [
            'data' => $data,
            'meta' => [
                'has_more' => $hasMore,
            ]
        ];
    }
}