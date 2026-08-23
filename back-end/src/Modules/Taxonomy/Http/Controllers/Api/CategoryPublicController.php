<?php

namespace Modules\Taxonomy\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Modules\Taxonomy\Services\Contracts\CategoryPublicServiceInterface;
use Modules\Taxonomy\Http\Resources\CategoryPublicResource;
use Modules\Taxonomy\Http\Requests\IndexCategoryPublicRequest;
use Illuminate\Routing\Controller;

class CategoryPublicController extends Controller
{
    public function __construct(
        private CategoryPublicServiceInterface $categoryPublicService,
    ) {}

    public function index(IndexCategoryPublicRequest $request): JsonResponse
    {
        $categories = $this->categoryPublicService->getPublicCategories(
            search: $request->validated('search'),
            perPage: $request->validated('per_page', 10)
        );

        return CategoryPublicResource::collection($categories)->response();
    }
}