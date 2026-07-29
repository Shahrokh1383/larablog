<?php

namespace Modules\Identity\Policies;

use Modules\Identity\Models\User;

class UserPolicy
{
    public function update(User $authUser, User $user): bool
    {
        return $authUser->id === $user->id;
    }

    public function delete(User $authUser, User $user): bool
    {
        return $authUser->id === $user->id;
    }
}