<?php

namespace Modules\Content\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Modules\Content\Services\CategoryPublicService;
use Modules\Content\Services\PostPublicService;
use Modules\Content\Http\Resources\CategoryPublicResource;
use Modules\Content\Http\Resources\PostPublicResource;
use Modules\Content\Http\Requests\IndexCategoryPublicRequest;
use Modules\Content\Http\Requests\ShowCategoryPostsRequest;
use Illuminate\Routing\Controller;

class CategoryPublicController extends Controller
{
    public function __construct(
        private CategoryPublicService $categoryPublicService,
        private PostPublicService $postPublicService
    ) {}

    public function index(IndexCategoryPublicRequest $request): JsonResponse
    {
        $categories = $this->categoryPublicService->getPublicCategories(
            search: $request->validated('search'),
            perPage: $request->validated('per_page', 10)
        );

        return CategoryPublicResource::collection($categories)->response();
    }

    public function posts(string $slug, ShowCategoryPostsRequest $request): JsonResponse
    {
        $posts = $this->postPublicService->getPostsByCategory(
            categorySlug: $slug,
            sort: $request->validated('sort', 'newest'),
            perPage: $request->validated('per_page', 10)
        );

        return PostPublicResource::collection($posts)->response();
    }
}