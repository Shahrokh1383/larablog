<?php

namespace Modules\Content\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Modules\Content\Services\PostService;
use Modules\Content\Http\Resources\PostPublicResource;
use Illuminate\Routing\Controller;

class PostPublicController extends Controller
{
    public function __construct(private PostService $postService) {}

    public function index(): JsonResponse
    {
        $posts = $this->postService->getHomeData();
        return PostPublicResource::collection($posts)->response();
    }

    public function show(string $slug): JsonResponse
    {
        $post = $this->postService->getBySlug($slug);
        if (!$post) {
            abort(404);
        }
        return (new PostPublicResource($post))->response();
    }

    public function related(string $slug): JsonResponse
    {
        $related = $this->postService->getRelatedPosts($slug);
        return PostPublicResource::collection($related)->response();
    }
}