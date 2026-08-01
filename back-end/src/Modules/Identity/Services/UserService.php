<?php

namespace Modules\Identity\Services;

use Modules\Identity\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class UserService
{
    public function getAllUsers(int $perPage = 15): LengthAwarePaginator
    {
        return User::with('roles')->latest()->paginate($perPage);
    }

    public function updateRole(User $user, string $role): User
    {
        $user->syncRoles([$role]);
        return $user->load('roles');
    }

    public function updatePassword(User $user, string $password): User
    {
        $user->update(['password' => $password]);
        return $user;
    }
}