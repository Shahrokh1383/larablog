<?php

namespace Modules\AdminStats\Policies;

use Shared\Contracts\HasRolesContract;

class AdminStatsPolicy
{
    public function viewDashboard(HasRolesContract $user): bool
    {
        return $user->hasAnyRole(['admin', 'editor']);
    }

    public function viewAuthors(HasRolesContract $user): bool
    {
        return $user->hasAnyRole(['admin', 'editor']);
    }
}