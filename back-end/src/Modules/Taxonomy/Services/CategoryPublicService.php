<?php

namespace Modules\Taxonomy\Services;

use Modules\Taxonomy\Models\Category;
use Modules\Taxonomy\Services\Contracts\CategoryPublicServiceInterface;
use Modules\Articles\Services\Contracts\PostStatsServiceInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CategoryPublicService implements CategoryPublicServiceInterface
{
    public function __construct(
        private PostStatsServiceInterface $postStatsService
    ) {}

    public function getPublicCategories(?string $search = null, int $perPage = 10): LengthAwarePaginator
    {
        $categories = Category::search($search)->paginate($perPage);
        $categoryIds = $categories->pluck('id')->toArray();

        $postCounts = $this->postStatsService->getPublishedPostCountsByCategories($categoryIds);
        $authorCounts = $this->postStatsService->getDistinctAuthorCountsByCategories($categoryIds);

        $categories->each(function ($category) use ($postCounts, $authorCounts) {
            $category->posts_count = $postCounts[$category->id] ?? 0;
            $category->authors_count = $authorCounts[$category->id] ?? 0;
        });

        return $categories;
    }

    public function getPublicCategoryBySlug(string $slug): Category
    {
        $category = Category::where('slug', $slug)->firstOrFail();

        $postCounts = $this->postStatsService->getPublishedPostCountsByCategories([$category->id]);
        $authorCounts = $this->postStatsService->getDistinctAuthorCountsByCategories([$category->id]);

        $category->posts_count = $postCounts[$category->id] ?? 0;
        $category->authors_count = $authorCounts[$category->id] ?? 0;

        return $category;
    }

    public function getCategoryIdBySlug(string $slug): ?string
    {
        return Category::where('slug', $slug)->value('id');
    }

    public function getPopularCategories(int $limit): array
    {
        $stats = $this->postStatsService->getPopularCategoryStats($limit);

        if (empty($stats)) {
            return [];
        }

        $categoryIds = array_column($stats, 'category_id');
        $categories = Category::whereIn('id', $categoryIds)->get()->keyBy('id');

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
        if (empty($ids)) {
            return [];
        }

        return Category::whereIn('id', $ids)
            ->get()
            ->mapWithKeys(fn($c) => [
                $c->id => [
                    'id'   => $c->id,
                    'name' => $c->name,
                    'slug' => $c->slug,
                ]
            ])
            ->all();
    }
}