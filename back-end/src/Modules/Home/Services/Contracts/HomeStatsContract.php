<?php

namespace Modules\Home\Services\Contracts;

interface HomeStatsContract
{
    public function getDashboardStats(): array;

    public function getAuthorStats(): array;
}