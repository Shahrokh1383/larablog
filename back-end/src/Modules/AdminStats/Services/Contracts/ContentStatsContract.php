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
}