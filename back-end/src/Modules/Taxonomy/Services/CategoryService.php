<?php

namespace Modules\Taxonomy\Services;

use Modules\Taxonomy\Models\Category;
use Modules\Taxonomy\DTOs\CategoryCreateDTO;
use Modules\Taxonomy\Actions\GenerateSlugAction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CategoryService
{
    public function __construct(
        private GenerateSlugAction $generateSlugAction
    ) {}

    public function getAll(int $perPage = 15, int $page = 1): LengthAwarePaginator
    {
        return Category::withCount('posts')
            ->orderBy('name')
            ->paginate($perPage, ['*'], 'page', $page);
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