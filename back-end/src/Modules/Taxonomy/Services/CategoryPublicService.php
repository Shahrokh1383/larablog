<?php

namespace Modules\Taxonomy\Services;

use Modules\Taxonomy\Models\Category;
use Modules\Taxonomy\Services\Contracts\CategoryPublicServiceInterface;
use Modules\Articles\Services\Contracts\PostPublicServiceInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CategoryPublicService implements CategoryPublicServiceInterface
{
    public function __construct(
        private PostPublicServiceInterface $postPublicService
    ) {}

    public function getPublicCategories(?string $search = null, int $perPage = 10): LengthAwarePaginator
    {
        $categories = Category::search($search)->paginate($perPage);
        $categoryIds = $categories->pluck('id')->toArray();

        // Fetch aggregates strictly via Contract (Map Pattern)
        $postCounts = $this->postPublicService->getPublishedPostCountsByCategories($categoryIds);
        $authorCounts = $this->postPublicService->getDistinctAuthorCountsByCategories($categoryIds);

        $categories->each(function ($category) use ($postCounts, $authorCounts) {
            $category->posts_count = $postCounts[$category->id] ?? 0;
            $category->authors_count = $authorCounts[$category->id] ?? 0;
        });

        return $categories;
    }

    public function getPublicCategoryBySlug(string $slug): Category
    {
        $category = Category::where('slug', $slug)->firstOrFail();
        
        $postCounts = $this->postPublicService->getPublishedPostCountsByCategories([$category->id]);
        $authorCounts = $this->postPublicService->getDistinctAuthorCountsByCategories([$category->id]);

        $category->posts_count = $postCounts[$category->id] ?? 0;
        $category->authors_count = $authorCounts[$category->id] ?? 0;

        return $category;
    }

    public function getCategoryIdBySlug(string $slug): string
    {
        return Category::where('slug', $slug)->firstOrFail()->id;
    }

    public function getPopularCategories(int $limit): array
    {
        // 1. Ask Articles for the top category IDs and their counts
        $stats = $this->postPublicService->getPopularCategoryStats($limit);
        if (empty($stats)) return [];

        // 2. Fetch the actual Category models by those IDs
        $categoryIds = array_column($stats, 'category_id');
        $categories = Category::whereIn('id', $categoryIds)->get()->keyBy('id');

        // 3. Map and preserve the exact order returned by Articles
        $result = [];
        foreach ($stats as $stat) {
            $category = $categories[$stat['category_id']] ?? null;
            if ($category) {
                $result[] = [
                    'id'          => $category->id,
                    'name'        => $category->name,
                    'slug'        => $category->slug,
                    'posts_count' => (int) $stat['posts_count'],
                ];
            }
        }

        return $result;
    }

    public function getCategoryStats(): array
    {
        return [
            'total_categories' => Category::count(),
        ];
    }

    public function getCategoriesByIds(array $ids): array
    {
        if (empty($ids)) return [];

        return Category::whereIn('id', $ids)
            ->get()
            ->mapWithKeys(fn($c) => [
                $c->id => [
                    'id' => $c->id,
                    'name' => $c->name,
                    'slug' => $c->slug,
                ]
            ])
            ->all();
    }
}