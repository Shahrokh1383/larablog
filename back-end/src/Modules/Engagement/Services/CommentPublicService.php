<?php

namespace Modules\Engagement\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\CursorPaginator;
use Modules\Engagement\Models\Comment;
use Modules\Profile\Services\Contracts\FetchesPublicProfiles;

class CommentPublicService
{
    /** Top-level comments per cursor page. */
    public const COMMENTS_PER_PAGE = 15;
    public const PRELOADED_REPLIES_LIMIT = 2;
    public const REPLIES_PER_PAGE = 10;
    public const ORDER_COLUMNS = ['created_at', 'id'];

    public function __construct(
        private FetchesPublicProfiles $profileFetcher
    ) {}

    public function getCommentsForPost(string $postId, ?string $cursor = null): CursorPaginator
    {
        $query = Comment::where('post_id', $postId)
            ->approved()
            ->whereNull('parent_id')
            ->with('user')
            ->withCount(['replies as replies_count' => fn ($countQuery) => $countQuery->approved()]);

        $this->applyDeterministicOrder($query);

        $paginator = $query->cursorPaginate(self::COMMENTS_PER_PAGE, ['*'], 'cursor', $cursor);

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
        $query = Comment::where('parent_id', $commentId)
            ->approved()
            ->with('user');

        $this->applyDeterministicOrder($query);

        // Fetch one extra row to detect whether another page exists.
        $replies = $query->skip($skip)->take($take + 1)->get();

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
        $parentIds = array_map(fn (Comment $comment) => $comment->id, $comments);

        if ($parentIds === []) {
            return;
        }

        $windowOrder = implode(', ', array_map(
            fn (string $column) => "engagement_comments.{$column} DESC",
            self::ORDER_COLUMNS
        ));

        $ranked = Comment::query()
            ->select('engagement_comments.*')
            ->selectRaw(
                "ROW_NUMBER() OVER ("
                    . "PARTITION BY engagement_comments.parent_id "
                    . "ORDER BY {$windowOrder}"
                    . ") AS reply_rank"
            )
            ->approved()
            ->whereIn('engagement_comments.parent_id', $parentIds);

        $outer = Comment::query()
            ->fromSub($ranked, 'ranked_replies')
            ->where('reply_rank', '<=', self::PRELOADED_REPLIES_LIMIT)
            ->with('user');

        // Preserves per-parent recency order after grouping; matches the
        // ordering of getRepliesForComment so expanding a thread never
        // reorders already-rendered replies.
        $this->applyDeterministicOrder($outer);

        $replies = $outer->get()->groupBy('parent_id');

        foreach ($comments as $comment) {
            $comment->setRelation(
                'replies',
                $replies->get($comment->id, collect())->values()
            );
        }
    }

    private function applyDeterministicOrder(Builder $query): void
    {
        foreach (self::ORDER_COLUMNS as $column) {
            $query->orderByDesc($column);
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