<?php

namespace Modules\Content\Services;

use Modules\Content\Models\Category;
use Modules\Content\Actions\GenerateSlugAction;
use Modules\Content\DTOs\CategoryCreateDTO;

class CategoryService
{
    public function __construct(
        private GenerateSlugAction $generateSlugAction
    ) {}

    public function getAll(int $perPage = 15, int $page = 1): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return Category::orderBy('name')->paginate  ($perPage, ['*'], 'page', $page);
    }

    public function create(CategoryCreateDTO $dto): Category
    {
        $slug = $this->generateSlugAction->execute($dto->name, Category::class);
        return Category::create([
            'name' => $dto->name,
            'slug' => $slug,
        ]);
    }

    public function update(Category $category, string $name): Category
    {
        if ($name !== $category->name) {
            $slug = $this->generateSlugAction->execute($name, Category::class, $category->id);
            $category->update(['name' => $name, 'slug' => $slug]);
        }
        return $category;
    }

    public function delete(Category $category): void
    {
        $category->delete();
    }
}