<?php

namespace Modules\Content\Http\Controllers\Api;

use Modules\Content\Models\Category;
use Modules\Content\Services\CategoryService;
use Modules\Content\Http\Requests\StoreCategoryRequest;
use Modules\Content\Http\Requests\UpdateCategoryRequest;
use Modules\Content\Http\Resources\CategoryResource;
use Modules\Content\DTOs\CategoryCreateDTO;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class CategoryController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private CategoryService $categoryService
    ) {
        $this->authorizeResource(Category::class, 'category');
    }

    public function index()
    {
        $categories = $this->categoryService->getAll();
        return CategoryResource::collection($categories);
    }

    public function store(StoreCategoryRequest $request): CategoryResource
    {
        $dto = new CategoryCreateDTO(name: $request->validated('name'));
        $category = $this->categoryService->create($dto);
        return new CategoryResource($category);
    }

    public function show(Category $category): CategoryResource
    {
        return new CategoryResource($category);
    }

    public function update(UpdateCategoryRequest $request, Category $category): CategoryResource
    {
        $category = $this->categoryService->update($category, $request->validated('name'));
        return new CategoryResource($category);
    }

    public function destroy(Category $category): JsonResponse
    {
        $this->categoryService->delete($category);
        return response()->json(null, 204);
    }
}