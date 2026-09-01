<?php

namespace Modules\AdminStats\Services;

use Illuminate\Support\Facades\Cache;
use Modules\AdminStats\Services\Contracts\ContentStatsContract;
use Modules\Articles\Services\Contracts\PostAdminStatsServiceInterface;
use Modules\Engagement\Services\Contracts\CommentServiceInterface;
use Modules\Taxonomy\Services\Contracts\CategoryAdminServiceInterface;
use Modules\Taxonomy\Services\Contracts\TagAdminServiceInterface;

class ContentStatsService implements ContentStatsContract
{
    public const TOP_COMMENTERS_CACHE_KEY = 'admin_top_commenters';
    private const TOP_COMMENTERS_TTL_MINUTES = 5;

    public function __construct(
        private PostAdminStatsServiceInterface $postAdminService,
        private CategoryAdminServiceInterface $categoryAdminService,
        private TagAdminServiceInterface $tagAdminService,
        private CommentServiceInterface $commentService
    ) {}

    public function getDashboardStats(): array
    {
        // 1. Fetch aggregate stats strictly via Contract (Article IV)
        $totalPosts = $this->postAdminService->getTotalPostsCount();
        $publishedPosts = $this->postAdminService->getPublishedPostsCount();
        $totalViews = $this->postAdminService->getTotalViews();

        // 2. Fetch popular categories via Map Pattern (No Model Import)
        $catStats = $this->postAdminService->getPopularCategoryStats(5);
        $catIds = array_column($catStats, 'category_id');
        $cats = $this->categoryAdminService->getByIds($catIds);

        $popularCategories = [];
        foreach ($catStats as $stat) {
            if ($cat = $cats[$stat['category_id']] ?? null) {
                $popularCategories[] = [
                    'id'          => $cat['id'],
                    'name'        => $cat['name'],
                    'slug'        => $cat['slug'],
                    'posts_count' => (int) $stat['posts_count']
                ];
            }
        }

        // 3. Fetch popular tags via Map Pattern (No Model Import)
        $tagStats = $this->postAdminService->getPopularTagStats(10);
        $tagIds = array_column($tagStats, 'tag_id');
        $tags = $this->tagAdminService->getByIds($tagIds);

        $popularTags = [];
        foreach ($tagStats as $stat) {
            if ($tag = $tags[$stat['tag_id']] ?? null) {
                $popularTags[] = [
                    'id'          => $tag['id'],
                    'name'        => $tag['name'],
                    'slug'        => $tag['slug'],
                    'posts_count' => (int) $stat['posts_count']
                ];
            }
        }

        return [
            'total_posts'        => $totalPosts,
            'published_posts'    => $publishedPosts,
            'total_views'        => $totalViews,
            'popular_categories' => $popularCategories,
            'popular_tags'       => $popularTags,
        ];
    }

    public function getAuthorDashboardStats(string $userId): array
    {
        $stats = $this->postAdminService->getAuthorStatsForUserId($userId);

        return [
            'posts_count' => (int) ($stats['posts_count'] ?? 0),
            'total_views' => (int) ($stats['total_views'] ?? 0),
        ];
    }

    public function getTopCommenters(int $limit = 10): array
    {
        return Cache::remember(
            self::TOP_COMMENTERS_CACHE_KEY,
            now()->addMinutes(self::TOP_COMMENTERS_TTL_MINUTES),
            fn (): array => $this->commentService->getAllTimeTopCommenters($limit)
        );
    }

    public function getAuthorStats(): array
    {
        return $this->postAdminService->getAuthorStats();
    }

    public function getAuthorStatsForUserIds(array $userIds): array
    {
        return $this->postAdminService->getAuthorStatsForUserIds($userIds);
    }

    public function getAuthorStatsForUserId(string $userId): array
    {
        return $this->postAdminService->getAuthorStatsForUserId($userId);
    }
}