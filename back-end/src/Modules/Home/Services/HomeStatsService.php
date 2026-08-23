<?php

namespace Modules\Home\Services;

use Modules\Home\Services\Contracts\HomeStatsContract;
use Modules\Articles\Services\Contracts\PostStatsServiceInterface;
use Modules\Taxonomy\Services\Contracts\CategoryPublicServiceInterface;
use Modules\Taxonomy\Services\Contracts\TagPublicServiceInterface;

class HomeStatsService implements HomeStatsContract
{
    public function __construct(
        private PostStatsServiceInterface $postStatsService,
        private CategoryPublicServiceInterface $categoryPublicService,
        private TagPublicServiceInterface $tagPublicService,
    ) {}

    public function getDashboardStats(): array
    {
        return [
            'total_posts'        => $this->postStatsService->getTotalPostsCount(),
            'published_posts'    => $this->postStatsService->getPublishedPostsCount(),
            'total_views'        => $this->postStatsService->getTotalViews(),
            'popular_categories' => $this->categoryPublicService->getPopularCategories(5),
            'popular_tags'       => $this->tagPublicService->getPopularTags(10),
        ];
    }

    public function getAuthorStats(): array
    {
        return $this->postStatsService->getAuthorStats();
    }
}