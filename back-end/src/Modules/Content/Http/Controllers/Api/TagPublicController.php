<?php

namespace Modules\Content\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Modules\Content\Services\TagPublicService;
use Modules\Content\Services\PostPublicService;
use Modules\Content\Http\Resources\TagPublicResource;
use Modules\Content\Http\Resources\PostPublicResource;
use Modules\Content\Http\Requests\IndexTagPublicRequest;
use Modules\Content\Http\Requests\ShowTagPostsRequest;
use Illuminate\Routing\Controller;

class TagPublicController extends Controller
{
    public function __construct(
        private TagPublicService $tagPublicService,
        private PostPublicService $postPublicService
    ) {}

    public function index(IndexTagPublicRequest $request): JsonResponse
    {
        $tags = $this->tagPublicService->getPublicTags(
            search: $request->validated('search'),
            perPage: $request->validated('per_page', 12)
        );

        return TagPublicResource::collection($tags)->response();
    }

    public function popular(): JsonResponse
    {
        $tags = $this->tagPublicService->getPopularTags();
        return TagPublicResource::collection($tags)->response();
    }

    public function posts(string $slug, ShowTagPostsRequest $request): JsonResponse
    {
        $tag = $this->tagPublicService->getPublicTagBySlug($slug);

        $posts = $this->postPublicService->getPostsByTag(
            tagSlug: $slug,
            sort: $request->validated('sort', 'newest'),
            perPage: $request->validated('per_page', 10)
        );

        return response()->json([
            'tag' => new TagPublicResource($tag),
            'posts' => [
                'data' => PostPublicResource::collection($posts->items()),
                'meta' => [
                    'current_page' => $posts->currentPage(),
                    'last_page'    => $posts->lastPage(),
                    'per_page'     => $posts->perPage(),
                    'total'        => $posts->total(),
                ],
            ],
        ]);
    }
}