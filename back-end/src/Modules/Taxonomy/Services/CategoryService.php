<?php

namespace Modules\Taxonomy\Services;

use Modules\Taxonomy\Models\Category;
use Modules\Articles\Services\Contracts\PostAdminServiceInterface;
use Modules\Taxonomy\Services\Contracts\CategoryAdminServiceInterface;
use Shared\Actions\GenerateSlugAction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
class CategoryService implements CategoryAdminServiceInterface
{
    public function __construct(
        private GenerateSlugAction $generateSlugAction,
        private PostAdminServiceInterface $postAdminService
    ) {}

    public function getAll(int $perPage = 15, int $page = 1): LengthAwarePaginator
    {
        $categories = Category::orderBy('name')->paginate($perPage, ['*'], 'page', $page);
        $categoryIds = $categories->pluck('id')->toArray();

        $counts = $this->postAdminService->getTotalPostCountsByCategories($categoryIds);

        $categories->each(function ($category) use ($counts) {
            $category->posts_count = $counts[$category->id] ?? 0;
        });

        return $categories;
    }

    public function getWithStats(Category $category): Category
    {
        $counts = $this->postAdminService->getTotalPostCountsByCategories([$category->id]);
        $category->posts_count = $counts[$category->id] ?? 0;

        return $category;
    }

    public function create(string $name): Category
    {
        $slug = $this->generateSlugAction->execute($name, Category::class);

        return Category::create([
            'name' => $name,
            'slug' => (string) $slug,
        ]);
    }

    public function update(Category $category, string $name): Category
    {
        if ($name !== $category->name) {
            $slug = $this->generateSlugAction->execute($name, Category::class, $category->id);
            $category->update(['name' => $name, 'slug' => (string) $slug]);
        }

        return $this->getWithStats($category);
    }

    public function delete(Category $category): void
    {
        $category->delete();
    }

    public function getByIds(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }

        return Category::whereIn('id', $ids)
            ->get(['id', 'name', 'slug'])
            ->mapWithKeys(fn($cat) => [
                $cat->id => [
                    'id'   => $cat->id,
                    'name' => $cat->name,
                    'slug' => $cat->slug,
                ]
            ])
            ->toArray();
    }
}