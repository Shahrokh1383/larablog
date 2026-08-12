<?php

namespace Modules\Search\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Search\Http\Requests\SearchRequest;
use Modules\Search\Services\SearchService;
use Modules\Content\Http\Resources\PostPublicResource; // Reusing existing resource for frontend sync

class SearchController extends Controller
{
    public function __construct(private SearchService $searchService) {}

    public function index(SearchRequest $request): JsonResponse
    {
        $term = $request->validated('q');
        $perPage = $request->integer('per_page', 10);

        $posts = $this->searchService->searchPosts($term, $perPage);

        return PostPublicResource::collection($posts)->response();
    }
}