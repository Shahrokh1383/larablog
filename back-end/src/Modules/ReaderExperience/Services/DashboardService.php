<?php

namespace Modules\ReaderExperience\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
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

        $readQuery = PostRead::where('user_id', $userId)
            ->whereBetween('read_at', [$startOfWeek, $endOfWeek]);

        $postsReadCount = (clone $readQuery)->count();

        $postIdsSubquery = function ($query) use ($userId, $startOfWeek, $endOfWeek) {
            $query->select('post_id')
                  ->from('reader_post_reads')
                  ->where('user_id', $userId)
                  ->whereBetween('read_at', [$startOfWeek, $endOfWeek]);
        };
        
        $totalReadingTime = $this->postInfoService->getTotalReadingTimeByIds($postIdsSubquery);

        $commentsCount = $this->commentService->getWeeklyCommentCountForUser($userId);

        $topCommenters = Cache::remember('weekly_top_commenters', 3600, function () {
            return $this->commentService->getWeeklyTopCommenters(10);
        });
        $isTopCommenter = $topCommenters->contains('user_id', $userId);

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