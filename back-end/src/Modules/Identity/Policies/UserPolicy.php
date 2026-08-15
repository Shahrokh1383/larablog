<?php

namespace Modules\Identity\Policies;

use Modules\Identity\Models\User;

class UserPolicy
{
    /**
     * Admins can do everything. This runs before other policy methods.
     */
    public function before(User $authUser, string $ability): ?bool
    {
        if ($authUser->hasRole(config('permissions.admin_roles'))) {
            return true;
        }

        return null;
    }

    public function viewAny(User $authUser): bool
    {
        return $authUser->hasRole(config('permissions.admin_roles'));
    }

    public function updateRole(User $authUser, User $user): bool
    {
        return $authUser->hasRole(config('permissions.admin_roles'));
    }

    public function updatePassword(User $authUser, User $user): bool
    {
        return $authUser->hasRole(config('permissions.admin_roles'));
    }

    public function update(User $authUser, User $user): bool
    {
        return $authUser->id === $user->id;
    }

    public function delete(User $authUser, User $user): bool
    {
        return $authUser->hasRole(config('permissions.admin_roles'));
    }
}