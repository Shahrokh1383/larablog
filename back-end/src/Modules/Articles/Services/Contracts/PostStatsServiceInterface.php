<?php

namespace Modules\Articles\Services\Contracts;

interface PostStatsServiceInterface
{
    public function getTotalPostsCount(): int;
    public function getPublishedPostsCount(): int;
    public function getTotalViews(): int;
    
    public function getAuthorStats(): array;
    
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