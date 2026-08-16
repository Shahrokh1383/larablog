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
        return Category::search($search)
            ->withCount([
                'posts as posts_count' => fn($q) => $q->where('content_posts.is_published', true),
                'posts as authors_count' => fn($q) => $q->where('content_posts.is_published', true)->select(DB::raw('count(distinct user_id)'))
            ])
            ->with(['posts' => fn($q) => $q->where('content_posts.is_published', true)->latest('published_at')->take(4)])
            ->paginate($perPage);
    }

    public function getPublicCategoryBySlug(string $slug): Category
    {
        return Category::where('slug', $slug)
            ->withCount([
                'posts as posts_count' => fn($q) => $q->where('content_posts.is_published', true),
                'posts as authors_count' => fn($q) => $q->where('content_posts.is_published', true)->select(DB::raw('count(distinct user_id)'))
            ])
            ->firstOrFail();
    }

    public function getCategoryIdBySlug(string $slug): string
    {
        return Category::where('slug', $slug)->firstOrFail()->id;
    }

    public function getPopularCategories(int $limit): array
    {
        return Category::withCount([
                'posts as posts_count' => fn($q) => $q->where('content_posts.is_published', true)
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