<?php

namespace Modules\Articles\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Modules\Articles\Services\PostPublicService;
use Modules\Articles\Http\Resources\PostPublicResource;
use Modules\Articles\Http\Requests\IndexPostsByCategoryRequest;
use Modules\Articles\Http\Requests\IndexPostsByTagRequest;
use Illuminate\Routing\Controller;

class PostPublicController extends Controller
{
    public function __construct(private PostPublicService $postPublicService) {}

    public function index(): JsonResponse
    {
        $posts = $this->postPublicService->getPaginatedPosts();
        return PostPublicResource::collection($posts)->response();
    }

    public function show(string $slug): JsonResponse
    {
        $post = $this->postPublicService->getBySlug($slug);
        if (!$post) {
            abort(404);
        }
        return (new PostPublicResource($post))->response();
    }

    public function related(string $slug): JsonResponse
    {
        $related = $this->postPublicService->getRelatedPosts($slug);
        return PostPublicResource::collection($related)->response();
    }

    public function postsByCategory(string $categorySlug, IndexPostsByCategoryRequest $request): JsonResponse
    {
        $result = $this->postPublicService->getPublishedPostsByCategoryForPublic(
            categorySlug: $categorySlug,
            sort: $request->validated('sort', 'newest'),
            perPage: $request->validated('per_page', 10)
        );

        $postsJson = PostPublicResource::collection($result['posts'])->response()->getData(true);

        return response()->json([
            'category' => $result['category'],
            'posts' => $postsJson,
        ]);
    }

    public function postsByTag(string $tagSlug, IndexPostsByTagRequest $request): JsonResponse
    {
        $result = $this->postPublicService->getPublishedPostsByTagForPublic(
            tagSlug: $tagSlug,
            sort: $request->validated('sort', 'newest'),
            perPage: $request->validated('per_page', 10)
        );

        $postsJson = PostPublicResource::collection($result['posts'])->response()->getData(true);

        return response()->json([
            'tag' => $result['tag'],
            'posts' => $postsJson,
        ]);
    }
}