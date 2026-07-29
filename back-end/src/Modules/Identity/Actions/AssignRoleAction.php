<?php

namespace Modules\Identity\Actions;

use Modules\Identity\Models\User;

class AssignRoleAction
{
    public function execute(User $user, string $role = 'user'): User
    {
        $user->assignRole($role);
        return $user;
    }
}