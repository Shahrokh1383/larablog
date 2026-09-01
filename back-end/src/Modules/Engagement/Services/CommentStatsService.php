<?php

namespace Modules\Engagement\Services;

use Modules\Engagement\Models\Comment;
use Modules\Engagement\Services\Contracts\CommentServiceInterface;
use Modules\Articles\Services\Contracts\PostInfoContract;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Carbon\Carbon;

class CommentStatsService implements CommentServiceInterface
{
    public function __construct(
        private readonly PostInfoContract $postInfoService
    ) {}

    public function getCommentCountsForPosts(array $postIds): array
    {
        if (empty($postIds)) return [];

        return Comment::whereIn('post_id', $postIds)
            ->approved()
            ->selectRaw('post_id, count(*) as aggregate')
            ->groupBy('post_id')
            ->pluck('aggregate', 'post_id')
            ->toArray();
    }

    public function getUserCommentsPaginated(string $userId, int $perPage = 15): LengthAwarePaginator
    {
        $paginated = Comment::where('user_id', $userId)
            ->approved()
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        // Pragmatic Boundary Respect: Resolve Post data via Content Service Contract
        $postIds = $paginated->getCollection()->pluck('post_id')->unique()->toArray();
        $postsMap = $this->postInfoService->getPostsByIds($postIds);

        // Append post info to each comment item dynamically without leaking models
        $paginated->getCollection()->transform(function (Comment $comment) use ($postsMap) {
            $postInfo = $postsMap[$comment->post_id] ?? null;
            $comment->post_slug = $postInfo->slug ?? null;
            $comment->post_title = $postInfo->title ?? null;
            return $comment;
        });

        return $paginated;
    }

    public function getWeeklyTopCommenters(int $limit = 10): Collection
    {
        $startOfWeek = Carbon::now()->startOfWeek();

        return Comment::where('created_at', '>=', $startOfWeek)
            ->approved()
            ->whereNotNull('user_id')
            ->selectRaw('user_id, count(*) as comments_count')
            ->groupBy('user_id')
            ->orderByDesc('comments_count')
            ->limit($limit)
            ->get();
    }

    public function getAllTimeTopCommenters(int $limit = 10): array
    {
        return Comment::query()
            ->approved()
            ->leftJoin('users as u', 'u.id', '=', 'engagement_comments.user_id')
            ->selectRaw(
                'MAX(engagement_comments.user_id) as user_id, ' .
                'COALESCE(MAX(u.name), MAX(engagement_comments.name)) as name, ' .
                'COALESCE(MAX(u.email), MAX(engagement_comments.email)) as email, ' .
                'COUNT(*) as comments_count'
            )
            ->groupByRaw('COALESCE(engagement_comments.user_id, engagement_comments.email)')
            ->orderByDesc('comments_count')
            ->limit($limit)
            ->get()
            ->map(fn (Comment $row): array => [
                'user_id'        => $row->user_id,
                'name'           => $row->name,
                'email'          => $row->email,
                'comments_count' => (int) $row->comments_count,
            ])
            ->all();
    }

    public function getWeeklyCommentCountForUser(string $userId): int
    {
        $startOfWeek = Carbon::now()->startOfWeek();

        return Comment::where('user_id', $userId)
            ->where('created_at', '>=', $startOfWeek)
            ->approved()
            ->count();
    }

    public function getTotalCommentCountForUser(string $userId): int
    {
        return Comment::where('user_id', $userId)
            ->approved()
            ->count();
    }
}