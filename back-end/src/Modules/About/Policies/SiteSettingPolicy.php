<?php

namespace Modules\About\Policies;

use Shared\Contracts\HasRolesContract;

class SiteSettingPolicy
{
    public function before(HasRolesContract $authUser): ?bool
    {
        if ($authUser->hasRole('admin')) {
            return true;
        }
        return null;
    }

    public function update(HasRolesContract $authUser): bool
    {
        return false; // Only admin passes through before()
    }
}