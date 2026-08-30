<?php

namespace Modules\AdminStats\Services\Contracts;

interface ContentStatsContract
{
    public function getDashboardStats(): array;
    
    /**
     * Get total posts count and total views grouped by user_id.
     * 
     * @return array<int, array{posts_count: int, total_views: int}>
     */
    public function getAuthorStats(): array;

    /**
     * Get stats for a specific list of user IDs.
     * 
     * @param array<string> $userIds
     * @return array<string, array{posts_count: int, total_views: int}>
     */
    public function getAuthorStatsForUserIds(array $userIds): array;

    /**
     * Get stats for a single user ID.
     * 
     * @param string $userId
     * @return array{posts_count: int, total_views: int}
     */
    public function getAuthorStatsForUserId(string $userId): array;
}