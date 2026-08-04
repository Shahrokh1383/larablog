<?php

namespace Modules\Content\Services\Contracts;

interface ContentStatsContract
{
    /**
     * Get aggregated content statistics for admin dashboard.
     * Returns raw arrays to avoid coupling between modules.
     */
    public function getDashboardStats(): array;
}