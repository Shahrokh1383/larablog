<?php

namespace Modules\Taxonomy\Http\Controllers\Api;

use Modules\Taxonomy\Models\Category;
use Modules\Taxonomy\Services\CategoryService;
use Modules\Taxonomy\Http\Requests\StoreCategoryRequest;
use Modules\Taxonomy\Http\Requests\UpdateCategoryRequest;
use Modules\Taxonomy\Http\Resources\CategoryResource;
use Modules\Taxonomy\DTOs\CategoryCreateDTO;
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
        $perPage = request()->query('per_page', 15);
        $page = request()->query('page', 1);

        return CategoryResource::collection(
            $this->categoryService->getAll($perPage, $page)
        );
    }

    public function store(StoreCategoryRequest $request): CategoryResource
    {
        $dto = new CategoryCreateDTO(name: $request->validated('name'));

        return new CategoryResource(
            $this->categoryService->create($dto)
        );
    }

    public function show(Category $category): CategoryResource
    {
        return new CategoryResource($category);
    }

    public function update(UpdateCategoryRequest $request, Category $category): CategoryResource
    {
        return new CategoryResource(
            $this->categoryService->update($category, $request->validated('name'))
        );
    }

    public function destroy(Category $category): JsonResponse
    {
        $this->categoryService->delete($category);

        return response()->json(null, 204);
    }
}