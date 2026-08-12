<?php

namespace Modules\Identity\Services;

use Modules\Identity\Models\User;
use Modules\Identity\Services\Contracts\UpdatesUserBasicInfo;
use Modules\Identity\Services\Contracts\FetchesUsersByRole;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class UserService implements UpdatesUserBasicInfo, FetchesUsersByRole
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
        $user->touch();
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

    public function getPaginatedUsersWithRoles(array $roles, ?string $search, int $perPage, array $excludedIds = []): LengthAwarePaginator
    {
        return User::with('roles')
            ->whereHas('roles', function ($query) use ($roles) {
                $query->whereIn('name', $roles);
            })
            ->when(!empty($excludedIds), function ($query) use ($excludedIds) {
                $query->whereNotIn('id', $excludedIds);
            })
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->latest('updated_at')
            ->paginate($perPage);
    }

    public function getUsersWithRolesMap(array $userIds): array
    {
        if (empty($userIds)) {
            return [];
        }

        $users = User::with('roles')->whereIn('id', $userIds)->get();

        return $users->mapWithKeys(function ($user) {
            return [
                $user->id => [
                    'id'    => $user->id,
                    'name'  => $user->name,
                    'email' => $user->email,
                    'roles' => $user->roles->pluck('name')->toArray(),
                ]
            ];
        })->all();
    }
}