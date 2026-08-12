<?php

namespace Modules\Identity\Services;

use Modules\Identity\Models\User;
use Modules\Identity\Services\Contracts\UpdatesUserBasicInfo;
use Modules\Identity\Services\Contracts\FetchesUsersByRole;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
class UserService implements UpdatesUserBasicInfo
{
    public function getAllUsers(int $perPage = 15, ?string $search = null): LengthAwarePaginator
    {
        return User::with('roles')
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate($perPage);
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

    public function updateName(string $userId, string $name): void
    {
        User::where('id', $userId)->update(['name' => $name]);
    }

    public function getUsersWithRoles(array $roles): Collection
    {
        return User::whereHas('roles', function ($query) use ($roles) {
            $query->whereIn('name', $roles);
        })->get();
    }
}