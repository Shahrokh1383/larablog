<?php

namespace Modules\ReaderExperience\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Modules\Content\Services\Contracts\PostInfoContract;
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
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        // 1. Posts read this week
        $readPosts = PostRead::where('user_id', $userId)
            ->whereBetween('read_at', [$startOfWeek, $endOfWeek])
            ->get();
            
        $postsReadCount = $readPosts->count();

        // 2. Total reading time (sum of distinct posts read this week)
        $postIds = $readPosts->pluck('post_id')->unique()->toArray();
        $postsMap = $this->postInfoService->getPostsByIds($postIds);
        $totalReadingTime = collect($postsMap)->sum(fn ($post) => $post->reading_time ?? 0);

        // 3. Comments made this week
        $commentsCount = $this->commentService->getWeeklyCommentCountForUser($userId);

        // 4. Top commenter check
        $topCommenters = $this->commentService->getWeeklyTopCommenters(10);
        $isTopCommenter = $topCommenters->contains('user_id', $userId);

        // 5. Total Stats for Profile Header
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
    }

    public function getRecentlyRead(string $userId, int $perPage = 15): LengthAwarePaginator
    {
        $paginator = PostRead::where('user_id', $userId)
            ->orderBy('read_at', 'desc')
            ->paginate($perPage);

        $postIds = $paginator->getCollection()->pluck('post_id')->unique()->toArray();
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