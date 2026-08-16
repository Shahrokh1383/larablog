<?php

namespace Modules\Home\Services;

use Modules\Home\Services\Contracts\HomeStatsContract;
use Modules\Articles\Services\Contracts\PostPublicServiceInterface;
use Modules\Taxonomy\Services\Contracts\CategoryPublicServiceInterface;
use Modules\Taxonomy\Services\Contracts\TagPublicServiceInterface;

class HomeStatsService implements HomeStatsContract
{
    public function __construct(
        private PostPublicServiceInterface $postPublicService,
        private CategoryPublicServiceInterface $categoryPublicService,
        private TagPublicServiceInterface $tagPublicService,
    ) {}

    public function getDashboardStats(): array
    {
        return [
            'total_posts'        => $this->postPublicService->getTotalPostsCount(),
            'published_posts'    => $this->postPublicService->getPublishedPostsCount(),
            'total_views'        => $this->postPublicService->getTotalViews(),
            'popular_categories' => $this->categoryPublicService->getPopularCategories(5),
            'popular_tags'       => $this->tagPublicService->getPopularTagsAsArray(10),
        ];
    }

    public function getAuthorStats(): array
    {
        return $this->postPublicService->getAuthorStats();
    }
}