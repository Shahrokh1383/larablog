<?php

namespace Modules\Search\Services;

use Modules\Content\Services\PostPublicService;
use Modules\Content\Services\CategoryPublicService;
use Modules\Content\Services\TagPublicService;
use Modules\Profile\Services\Contracts\ProfileServiceInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SearchService
{
    public function __construct(
        private PostPublicService $postPublicService,
        private CategoryPublicService $categoryPublicService,
        private TagPublicService $tagPublicService,
        private ProfileServiceInterface $profileService,
    ) {}

    public function globalSearch(string $term, int $postPerPage = 6, int $entityLimit = 5): array
    {
        // 1. Fetch paginated posts (for the main grid)
        $posts = $this->postPublicService->searchPosts($term, $postPerPage);

        // 2. Fetch limited categories, tags, and authors (for instant suggestions/dropdowns)
        $categories = $this->categoryPublicService->getPublicCategories(search: $term, perPage: $entityLimit)->items();
        $tags = $this->tagPublicService->getPublicTags(search: $term, perPage: $entityLimit)->items();
        $authors = $this->profileService->getAllPublicProfiles(search: $term, perPage: $entityLimit)->items();

        return [
            'posts'      => $posts,
            'categories' => $categories,
            'tags'       => $tags,
            'authors'    => $authors,
        ];
    }
}