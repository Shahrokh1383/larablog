<?php

namespace Modules\Taxonomy\Services;

use Modules\Taxonomy\Models\Category;
use Modules\Taxonomy\Services\Contracts\CategoryPublicServiceInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class CategoryPublicService implements CategoryPublicServiceInterface
{
    public function getPublicCategories(?string $search = null, int $perPage = 10): LengthAwarePaginator
    {
        $postsCountSubQuery = DB::table('content_posts')
            ->selectRaw('count(*)')
            ->whereColumn('category_id', 'content_categories.id')
            ->where('is_published', true);

        $authorsCountSubQuery = DB::table('content_posts')
            ->selectRaw('count(distinct user_id)')
            ->whereColumn('category_id', 'content_categories.id')
            ->where('is_published', true);

        return Category::select('content_categories.*')
            ->addSelect([
                'posts_count' => $postsCountSubQuery,
                'authors_count' => $authorsCountSubQuery,
            ])
            ->search($search)
            ->paginate($perPage);
    }

    public function getPublicCategoryBySlug(string $slug): Category
    {
        $postsCountSubQuery = DB::table('content_posts')
            ->selectRaw('count(*)')
            ->whereColumn('category_id', 'content_categories.id')
            ->where('is_published', true);

        $authorsCountSubQuery = DB::table('content_posts')
            ->selectRaw('count(distinct user_id)')
            ->whereColumn('category_id', 'content_categories.id')
            ->where('is_published', true);

        return Category::select('content_categories.*')
            ->addSelect([
                'posts_count' => $postsCountSubQuery,
                'authors_count' => $authorsCountSubQuery,
            ])
            ->where('slug', $slug)
            ->firstOrFail();
    }

    public function getCategoryIdBySlug(string $slug): string
    {
        return Category::where('slug', $slug)->firstOrFail()->id;
    }

    public function getPopularCategories(int $limit): array
    {
        $postsCountSubQuery = DB::table('content_posts')
            ->selectRaw('count(*)')
            ->whereColumn('category_id', 'content_categories.id')
            ->where('is_published', true);

        return Category::select('content_categories.*')
            ->addSelect([
                'posts_count' => $postsCountSubQuery,
            ])
            ->orderByDesc('posts_count')
            ->take($limit)
            ->get()
            ->map(fn(Category $category) => [
                'id'          => $category->id,
                'name'        => $category->name,
                'slug'        => $category->slug,
                'posts_count' => (int) $category->posts_count,
            ])
            ->toArray();
    }

    public function getCategoryStats(): array
    {
        return [
            'total_categories' => Category::count(),
        ];
    }
}