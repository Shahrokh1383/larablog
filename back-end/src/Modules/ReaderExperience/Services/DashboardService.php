<?php

namespace Modules\ReaderExperience\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Modules\Articles\Services\Contracts\PostInfoContract;
use Modules\Engagement\Services\Contracts\CommentServiceInterface;
use Modules\ReaderExperience\Models\PostRead;
use Modules\ReaderExperience\Models\SavedPost;

class DashboardService
{
    public function __construct(
        private PostInfoContract $postInfoService,
        private CommentServiceInterface $commentService
    ) {}

    public function getOverview(string $userId): array
    {
        return Cache::remember("dashboard_overview_{$userId}", now()->addMinutes(5), function () use ($userId) {
            $startOfWeek = Carbon::now()->startOfWeek();
            $endOfWeek = Carbon::now()->endOfWeek();

            // 1. Fetch IDs into a PHP array.
            // For new users, this returns [] instantly and prevents the subquery deadlock.
            $postIds = PostRead::where('user_id', $userId)
                ->whereBetween('read_at', [$startOfWeek, $endOfWeek])
                ->pluck('post_id')
                ->unique()
                ->values()
                ->toArray();

            $postsReadCount = count($postIds);

            // 2. Pass array directly. PostInfoService will short-circuit if empty.
            $totalReadingTime = $this->postInfoService->getTotalReadingTimeByIds($postIds);

            $commentsCount = $this->commentService->getWeeklyCommentCountForUser($userId);
            $topCommenters = Cache::remember('all_time_top_commenters', 300, function () {
                return $this->commentService->getAllTimeTopCommenters(10);
            });
            $isTopCommenter = collect($topCommenters)->contains(
                fn (array $commenter): bool => $commenter['user_id'] === $userId
            );

            $totalComments = $this->commentService->getTotalCommentCountForUser($userId);
            $totalSavedPosts = SavedPost::where('user_id', $userId)->count();

            return [
                'posts_read_count'   => $postsReadCount,
                'total_reading_time' => (int) $totalReadingTime,
                'comments_count'     => $commentsCount,
                'is_top_commenter'   => $isTopCommenter,
                'total_comments'     => $totalComments,
                'total_saved_posts'  => $totalSavedPosts,
            ];
        });
    }

    public function getRecentlyRead(string $userId, int $perPage = 15): LengthAwarePaginator
    {
        // Select only required columns to reduce memory footprint
        $paginator = PostRead::select(['id', 'post_id', 'read_at'])
            ->where('user_id', $userId)
            ->orderBy('read_at', 'desc')
            ->paginate($perPage);

        $postIds = $paginator->getCollection()->pluck('post_id')->unique()->toArray();

        // Short-circuit for new users
        if (empty($postIds)) {
            return $paginator;
        }

        $postsMap = $this->postInfoService->getPostsByIds($postIds);

        $paginator->getCollection()->transform(function (PostRead $postRead) use ($postsMap) {
            $postRead->post_info = $postsMap[$postRead->post_id] ?? null;
            return $postRead;
        });

        return $paginator;
    }

    public function getUserCommentsPaginated(string $userId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->commentService->getUserCommentsPaginated($userId, $perPage);
    }
}