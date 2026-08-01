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
        if ($authUser->hasRole('admin')) {
            return true;
        }
        
        return null; // Fall through to the specific method
    }

    public function update(User $authUser, User $user): bool
    {
        return $authUser->id === $user->id;
    }

    public function delete(User $authUser, User $user): bool
    {
        return $authUser->id === $user->id;
    }
}