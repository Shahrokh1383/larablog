<?php

namespace Modules\Administration\Services;

use Modules\Content\Services\Contracts\ContentStatsContract;

class DashboardService
{
    public function __construct(
        private ContentStatsContract $contentStats
    ) {}

    public function getStats(): array
    {
        // Currently only Content module provides stats.
        // In future phases, inject additional contracts:
        // - EngagementStatsContract (comments, replies)
        // - MarketingStatsContract (subscribers)
        // And merge their results here.
        
        return $this->contentStats->getDashboardStats();
    }
}