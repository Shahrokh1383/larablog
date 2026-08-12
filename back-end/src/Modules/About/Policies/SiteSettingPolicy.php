<?php

namespace Modules\About\Policies;

use Modules\Identity\Models\User;

class SiteSettingPolicy
{
    public function before(User $authUser): ?bool
    {
        if ($authUser->hasRole('admin')) {
            return true;
        }
        return null;
    }

    public function update(User $authUser): bool
    {
        return false; // Only admin passes through before()
    }
}