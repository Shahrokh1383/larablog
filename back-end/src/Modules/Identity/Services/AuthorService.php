<?php

namespace Modules\Identity\Services;

use Modules\Identity\Models\User;
use Modules\Identity\DTOs\AuthorDTO;
use Modules\Identity\Services\Contracts\AuthorServiceInterface;
use Illuminate\Support\Collection;

class AuthorService implements AuthorServiceInterface
{
    public function getByUserId(string|int $userId): ?AuthorDTO
    {
        $user = User::find($userId);
        return $user ? $this->toDTO($user) : null;
    }

    public function getByUserIds(array $userIds): array
    {
        $users = User::whereIn('id', $userIds)->get();
        return $users->mapWithKeys(function (User $user) {
            return [$user->id => $this->toDTO($user)];
        })->all();
    }

    public function getByUsername(string $username): ?AuthorDTO
    {
        $user = User::where('username', $username)->first();
        return $user ? $this->toDTO($user) : null;
    }

    public function getAllAuthors(): array
    {
        return User::has('posts')->get()->map(fn (User $u) => $this->toDTO($u))->all();
    }

    private function toDTO(User $user): AuthorDTO
    {
        return new AuthorDTO(
            id:       $user->id,
            name:     $user->name,
            username: $user->username,
            avatar:   $user->avatar,
            bio:      $user->bio,
        );
    }
}