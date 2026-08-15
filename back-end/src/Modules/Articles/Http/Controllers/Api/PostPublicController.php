<?php

namespace Modules\Articles\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Modules\Articles\Services\PostPublicService;
use Modules\Articles\Http\Resources\PostPublicResource;
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
}