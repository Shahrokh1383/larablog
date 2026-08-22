<?php

namespace Modules\Articles\Services\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface PostPublicServiceInterface
{
    public function getPaginatedPosts(int $perPage = 10): LengthAwarePaginator;
    public function getBySlug(string $slug): ?object;
    public function getRelatedPosts(string $slug, int $limit = 3): array;
    public function getPostsByCategory(string $categoryId, ?string $sort = 'newest', int $perPage = 10): LengthAwarePaginator;
    public function getPostsByTag(string $tagId, ?string $sort = 'newest', int $perPage = 10): LengthAwarePaginator;
    public function getPostsByAuthor(string $username, ?string $sort = 'newest', int $perPage = 6): LengthAwarePaginator;
    public function searchPosts(?string $term, int $perpage = 10): LengthAwarePaginator;
    public function getFeaturedPosts(int $limit = 4): Collection;
    public function getRecentPosts(int $limit = 6, array $excludeIds = []): Collection;
    public function getTotalPostsCount(): int;
    public function getPublishedPostsCount(): int;
    public function getTotalViews(): int;
    public function getAuthorStats(): array;
    public function getPublishedPostsByCategoryForPublic(string $categorySlug, ?string $sort = 'newest', int $perPage = 10): array;
    public function getPublishedPostsByTagForPublic(string $tagSlug, ?string $sort = 'newest', int $perPage = 10): array;

    /** @return array<string, int> Map of category_id => published post count */
    public function getPublishedPostCountsByCategories(array $categoryIds): array;
    
    /** @return array<string, int> Map of category_id => distinct author count */
    public function getDistinctAuthorCountsByCategories(array $categoryIds): array;
    
    /** @return array<int, array{category_id: string, posts_count: int}> */
    public function getPopularCategoryStats(int $limit): array;
    
    /** @return array<string, int> Map of tag_id => published post count */
    public function getPublishedPostCountsByTags(array $tagIds): array;
    
    /** @return array<string, int> Map of tag_id => sum of views */
    public function getPublishedPostViewsSumByTags(array $tagIds): array;
    
    /** @return array<int, array{tag_id: string, posts_count: int, total_views: int}> */
    public function getPopularTagStats(int $limit): array;
}