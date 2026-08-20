<?php

namespace Modules\Taxonomy\Services;

use Modules\Taxonomy\Models\Category;
use Modules\Taxonomy\Models\Tag;
use Modules\Taxonomy\Services\Contracts\ContentStatsContract;
use Modules\Articles\Services\Contracts\PostAdminServiceInterface;

class ContentStatsService implements ContentStatsContract
{
    public function __construct(
        private PostAdminServiceInterface $postAdminService
    ) {}

    public function getDashboardStats(): array
    {
        // 1. Fetch aggregate stats strictly via Contract (Article IV)
        $totalPosts = $this->postAdminService->getTotalPostsCount();
        $publishedPosts = $this->postAdminService->getPublishedPostsCount();
        $totalViews = $this->postAdminService->getTotalViews();

        // 2. Fetch popular categories via Map Pattern
        $catStats = $this->postAdminService->getPopularCategoryStats(5);
        $catIds = array_column($catStats, 'category_id');
        $cats = Category::whereIn('id', $catIds)->get()->keyBy('id');
        
        $popularCategories = [];
        foreach ($catStats as $stat) {
            if ($cat = $cats[$stat['category_id']] ?? null) {
                $popularCategories[] = [
                    'id'          => $cat->id, 
                    'name'        => $cat->name, 
                    'slug'        => $cat->slug,
                    'posts_count' => (int) $stat['posts_count']
                ];
            }
        }

        // 3. Fetch popular tags via Map Pattern
        $tagStats = $this->postAdminService->getPopularTagStats(10);
        $tagIds = array_column($tagStats, 'tag_id');
        $tags = Tag::whereIn('id', $tagIds)->get()->keyBy('id');
        
        $popularTags = [];
        foreach ($tagStats as $stat) {
            if ($tag = $tags[$stat['tag_id']] ?? null) {
                $popularTags[] = [
                    'id'          => $tag->id, 
                    'name'        => $tag->name, 
                    'slug'        => $tag->slug,
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
        // Delegate strictly to Articles Contract (Article IV)
        return $this->postAdminService->getAuthorStats();
    }
}