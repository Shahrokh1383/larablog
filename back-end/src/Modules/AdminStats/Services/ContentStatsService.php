<?php

namespace Modules\AdminStats\Services;

use Modules\AdminStats\Services\Contracts\ContentStatsContract;
use Modules\Articles\Services\Contracts\PostAdminStatsServiceInterface;
use Modules\Taxonomy\Services\Contracts\CategoryAdminServiceInterface;
use Modules\Taxonomy\Services\Contracts\TagAdminServiceInterface;
use Illuminate\Support\Facades\DB;

class ContentStatsService implements ContentStatsContract
{
    public function __construct(
        private PostAdminStatsServiceInterface $postAdminService,
        private CategoryAdminServiceInterface $categoryAdminService,
        private TagAdminServiceInterface $tagAdminService
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

    public function getAuthorStats(): array
    {
        return $this->postAdminService->getAuthorStats();
    }

    public function getAuthorStatsForUserIds(array $userIds): array
    {
        if (empty($userIds)) {
            return [];
        }

        // Adapt the query below to match your exact table/scopes structure
        return \Modules\Articles\Models\Post::select('user_id', DB::raw('count(*) as posts_count'), DB::raw('sum(views) as total_views'))
            ->whereIn('user_id', $userIds)
            ->published() // Remove if this scope doesn't exist on your Post model
            ->groupBy('user_id')
            ->get()
            ->mapWithKeys(function ($item) {
                return [
                    $item->user_id => [
                        'posts_count' => (int) $item->posts_count,
                        'total_views' => (int) $item->total_views,
                    ]
                ];
            })
            ->all();
    }

    public function getAuthorStatsForUserId(string $userId): array
    {
        $stats = \Modules\Articles\Models\Post::where('user_id', $userId)
            ->published()
            ->selectRaw('count(*) as posts_count, sum(views) as total_views')
            ->first();

        return [
            'posts_count' => (int) ($stats->posts_count ?? 0),
            'total_views' => (int) ($stats->total_views ?? 0),
        ];
    }
}