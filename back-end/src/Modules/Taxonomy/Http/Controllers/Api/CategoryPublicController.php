<?php

namespace Modules\Taxonomy\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Modules\Taxonomy\Services\CategoryPublicService;
use Modules\Taxonomy\Http\Resources\CategoryPublicResource;
use Modules\Taxonomy\Http\Requests\IndexCategoryPublicRequest;
use Modules\Taxonomy\Http\Requests\ShowCategoryPostsRequest;
use Modules\Articles\Services\Contracts\PostPublicServiceInterface;
use Modules\Articles\Http\Resources\PostPublicResource;
use Illuminate\Routing\Controller;

class CategoryPublicController extends Controller
{
    public function __construct(
        private CategoryPublicService $categoryPublicService,
        private PostPublicServiceInterface $postPublicService,
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
        $category = $this->categoryPublicService->getPublicCategoryBySlug($slug);
        $posts = $this->postPublicService->getPublishedPostsByCategoryForPublic(
            categorySlug: $slug,
            sort: $request->validated('sort', 'newest'),
            perPage: $request->validated('per_page', 10)
        );

        return response()->json([
            'category' => new CategoryPublicResource($category),
            'posts'    => PostPublicResource::collection($posts),
        ]);
    }
}