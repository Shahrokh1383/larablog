<?php

namespace Modules\Identity\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Modules\Identity\Events\UserDeleted;
use Modules\Identity\Events\UserNameUpdated;
use Modules\Identity\Models\User;
use Modules\Identity\Services\Contracts\DeletesUserAccount;
use Modules\Identity\Services\Contracts\FetchesUsersByRole;
use Modules\Identity\Services\Contracts\UpdatesUserBasicInfo;
use Shared\Models\User as SharedUser;

class UserService implements UpdatesUserBasicInfo, FetchesUsersByRole, DeletesUserAccount
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
        $user->tokens()->delete(); // Revoke all existing tokens

        return $user;
    }

    public function updateName(SharedUser $user, string $name): void
    {
        $user->name = $name;
        $user->save();

        // Dispatch event only after the surrounding transaction commits.
        DB::afterCommit(function () use ($user) {
            event(new UserNameUpdated($user));
        });
    }

    public function deleteAccount(string $userId): void
    {
        $user = User::findOrFail($userId);

        // Revoke all Sanctum tokens before hard-deleting the user.
        $user->tokens()->delete();
        $user->delete();

        // Dispatch event only after the transaction commits.
        DB::afterCommit(function () use ($user) {
            event(new UserDeleted($user));
        });
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