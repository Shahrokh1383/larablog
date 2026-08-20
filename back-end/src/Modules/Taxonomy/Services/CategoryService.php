<?php

namespace Modules\Taxonomy\Services;

use Modules\Taxonomy\Models\Category;
use Shared\Actions\GenerateSlugAction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class CategoryService
{
    public function __construct(
        private GenerateSlugAction $generateSlugAction
    ) {}

    public function getAll(int $perPage = 15, int $page = 1): LengthAwarePaginator
    {
        $postsCountSubQuery = DB::table('content_posts')
            ->selectRaw('count(*)')
            ->whereColumn('category_id', 'content_categories.id');

        return Category::select('content_categories.*')
            ->addSelect([
                'posts_count' => $postsCountSubQuery,
            ])
            ->orderBy('name')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function create(string $name): Category
    {
        $slug = $this->generateSlugAction->execute($name, Category::class);

        return Category::create([
            'name' => $name,
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