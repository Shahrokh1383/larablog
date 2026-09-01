<?php

namespace Modules\Articles\Services\Contracts;

interface PostAdminStatsServiceInterface
{
    public function getTotalPostsCount(): int;
    public function getPublishedPostsCount(): int;
    public function getTotalViews(): int;
    
    /** @return array<int, array{posts_count: int, total_views: int}> */
    public function getAuthorStats(): array;

    /** 
     * Get stats for a specific list of user IDs.
     * @param array<string> $userIds
     * @return array<string, array{posts_count: int, total_views: int}> 
     */
    public function getAuthorStatsForUserIds(array $userIds): array;

    /** 
     * Get stats for a single user ID.
     * @return array{posts_count: int, total_views: int} 
     */
    public function getAuthorStatsForUserId(string $userId): array;
    
    /** @return array<string, int> Map of category_id => total post count (including unpublished) */
    public function getTotalPostCountsByCategories(array $categoryIds): array;
    
    /** @return array<string, int> Map of tag_id => total post count (including unpublished) */
    public function getTotalPostCountsByTags(array $tagIds): array;

    /** @return array<int, array{category_id: string, posts_count: int}> Top categories by post count */
    public function getPopularCategoryStats(int $limit): array;

    /** @return array<int, array{tag_id: string, posts_count: int}> Top tags by post count */
    public function getPopularTagStats(int $limit): array;
}