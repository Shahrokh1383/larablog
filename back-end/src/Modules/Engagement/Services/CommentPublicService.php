<?php

namespace Modules\Engagement\Services;

use Modules\Engagement\Models\Comment;
use Modules\Profile\Services\Contracts\FetchesPublicProfiles;
use Illuminate\Pagination\CursorPaginator;

class CommentPublicService
{
    public function __construct(
        private FetchesPublicProfiles $profileFetcher
    ) {}

    public function getCommentsForPost(string $postId, ?string $cursor = null): CursorPaginator
    {
        $paginator = Comment::where('post_id', $postId)
            ->approved()
            ->whereNull('parent_id')
            ->with([
                'user',
                'replies' => fn($query) => $query->with('user')->latest()->limit(2),
            ])
            ->withCount(['replies as replies_count' => fn($query) => $query->approved()])
            ->latest()
            ->cursorPaginate(15, ['*'], 'cursor', $cursor);

        // Gather all user IDs from comments and replies, fetch their profile avatars
        $userIds = collect();

        foreach ($paginator->items() as $comment) {
            if ($comment->user_id) $userIds->push($comment->user_id);
            foreach ($comment->replies as $reply) {
                if ($reply->user_id) $userIds->push($reply->user_id);
            }
        }

        $profiles = $this->profileFetcher->getPublicProfilesMap($userIds->unique()->toArray());

        // Attach avatar to each comment
        foreach ($paginator->items() as $comment) {
            $this->attachAvatar($comment, $profiles);
            foreach ($comment->replies as $reply) {
                $this->attachAvatar($reply, $profiles);
            }
        }

        return $paginator;
    }

    public function getTotalCommentsCount(string $postId): int
    {
        return Comment::where('post_id', $postId)->approved()->count();
    }

    public function getRepliesForComment(string $commentId, int $skip = 2, int $take = 10): array
    {
        $replies = Comment::where('parent_id', $commentId)
            ->approved()
            ->with('user')
            ->latest()
            ->skip($skip)
            ->take($take + 1)
            ->get();

        $hasMore = $replies->count() > $take;
        $data = $hasMore ? $replies->slice(0, $take) : $replies;

        // Fetch avatars for loaded replies
        $userIds = $data->pluck('user_id')->filter()->unique()->toArray();
        $profiles = $this->profileFetcher->getPublicProfilesMap($userIds);
        foreach ($data as $reply) {
            $this->attachAvatar($reply, $profiles);
        }

        return [
            'data' => $data,
            'meta' => [
                'has_more' => $hasMore,
            ]
        ];
    }

    private function attachAvatar(Comment $comment, array $profiles): void
    {
        if ($comment->user_id && isset($profiles[$comment->user_id])) {
            $comment->avatar = $profiles[$comment->user_id]['avatar'] ?? null;
        } else {
            $comment->avatar = null;
        }
    }
}