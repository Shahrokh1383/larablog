<?php

namespace Modules\Search\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Search\Http\Requests\SearchRequest;
use Modules\Search\Services\SearchService;
use Modules\Articles\Http\Resources\PostPublicResource;
use Modules\Taxonomy\Http\Resources\CategoryPublicResource;
use Modules\Taxonomy\Http\Resources\TagPublicResource;
use Modules\Profile\Http\Resources\AuthorResource;

class SearchController extends Controller
{
    public function __construct(private SearchService $searchService) {}

    public function index(SearchRequest $request): JsonResponse
    {
        $term = $request->validated('q');
        
        $results = $this->searchService->globalSearch($term);

        // Return unified JSON structure.
        // Note: PostPublicResource::collection($paginator) automatically wraps pagination meta.
        return response()->json([
            'posts'      => PostPublicResource::collection($results['posts'])->response()->getData(true),
            'categories' => CategoryPublicResource::collection($results['categories']),
            'tags'       => TagPublicResource::collection($results['tags']),
            'authors'    => AuthorResource::collection($results['authors']),
        ]);
    }
}