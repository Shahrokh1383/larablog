<?php

namespace Modules\Search\Services;

use Modules\Content\Services\PostPublicService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SearchService
{
    public function __construct(
        private PostPublicService $postPublicService,
    ) {}

    public function searchPosts(string $term, int $perPage = 10): LengthAwarePaginator
    {
        // Delegates to the Content module's public service
        return $this->postPublicService->searchPosts($term, $perPage);
    }
}