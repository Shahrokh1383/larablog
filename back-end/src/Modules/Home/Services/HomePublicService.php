<?php

namespace Modules\Home\Services;

use Modules\Articles\Services\Contracts\PostPublicServiceInterface;
use Modules\Taxonomy\Services\Contracts\CategoryPublicServiceInterface;

class HomePublicService
{
    public function __construct(
        private PostPublicServiceInterface $postPublicService,
        private CategoryPublicServiceInterface $categoryPublicService,
    ) {}

    public function getHomeAggregatedData(): array
    {
        $featured = $this->postPublicService->getFeaturedPosts(4);

        $featuredIds = collect($featured)
            ->pluck('id')
            ->filter()
            ->toArray();

        $recent = $this->postPublicService->getRecentPosts(6, $featuredIds);

        $categories = $this->categoryPublicService->getPopularCategories(7);

        $totalPostsCount = $this->postPublicService->getPublishedPostsCount();

        return [
            'featured_posts'    => $featured,
            'recent_posts'      => $recent,
            'categories'        => $categories,
            'total_posts_count' => $totalPostsCount,
        ];
    }
}