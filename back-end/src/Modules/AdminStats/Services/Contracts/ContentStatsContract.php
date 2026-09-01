<?php

namespace Modules\AdminStats\Services\Contracts;

interface ContentStatsContract
{
    public function getDashboardStats(): array;

    /**
     * @param string $userId
     * @return array{posts_count: int, total_views: int}
     */
    public function getAuthorDashboardStats(string $userId): array;

    /**
     * @param int $limit
     * @return array<int, array{user_id: ?string, name: ?string, email: ?string, comments_count: int}>
     */
    public function getTopCommenters(int $limit = 10): array;

    /**
     * @return array<int, array{posts_count: int, total_views: int}>
     */
    public function getAuthorStats(): array;

    /**
     * @param array<string> $userIds
     * @return array<string, array{posts_count: int, total_views: int}>
     */
    public function getAuthorStatsForUserIds(array $userIds): array;

    /**
     * @param string $userId
     * @return array{posts_count: int, total_views: int}
     */
    public function getAuthorStatsForUserId(string $userId): array;
}