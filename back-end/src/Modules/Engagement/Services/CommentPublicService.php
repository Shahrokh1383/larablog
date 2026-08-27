<?php

namespace Modules\Engagement\Services;

use Modules\Engagement\Models\Comment;
use Modules\Profile\Services\Contracts\FetchesPublicProfiles;
use Illuminate\Pagination\CursorPaginator;

class CommentPublicService
{
    /** Top-level comments per cursor page. */
    public const COMMENTS_PER_PAGE = 15;

    public const PRELOADED_REPLIES_LIMIT = 2;

    /** Page size of the replies endpoint. */
    public const REPLIES_PER_PAGE = 10;

    public function __construct(
        private FetchesPublicProfiles $profileFetcher
    ) {}

    public function getCommentsForPost(string $postId, ?string $cursor = null): CursorPaginator
    {
        $paginator = Comment::where('post_id', $postId)
            ->approved()
            ->whereNull('parent_id')
            ->with('user')
            ->withCount(['replies as replies_count' => fn ($query) => $query->approved()])
            ->latest()
            ->cursorPaginate(self::COMMENTS_PER_PAGE, ['*'], 'cursor', $cursor);

        $comments = $paginator->items();
        $this->preloadLatestReplies($comments);

        $userIds = collect();
        foreach ($comments as $comment) {
            if ($comment->user_id) $userIds->push($comment->user_id);
            foreach ($comment->replies as $reply) {
                if ($reply->user_id) $userIds->push($reply->user_id);
            }
        }

        $profiles = $this->profileFetcher->getPublicProfilesMap($userIds->unique()->toArray());

        foreach ($comments as $comment) {
            $comment->replies_has_more = $comment->replies_count > self::PRELOADED_REPLIES_LIMIT;
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

    public function getRepliesForComment(
        string $commentId,
        int $skip = self::PRELOADED_REPLIES_LIMIT,
        int $take = self::REPLIES_PER_PAGE,
    ): array {
        // Fetch one extra row to detect whether another page exists.
        $replies = Comment::where('parent_id', $commentId)
            ->approved()
            ->with('user')
            ->latest()
            ->skip($skip)
            ->take($take + 1)
            ->get();

        $hasMore = $replies->count() > $take;
        $data = $hasMore ? $replies->slice(0, $take) : $replies;

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

    private function preloadLatestReplies(array $comments): void
    {
        foreach ($comments as $comment) {
            $comment->setRelation(
                'replies',
                $comment->replies()->with('user')->limit(self::PRELOADED_REPLIES_LIMIT)->get()
            );
        }
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