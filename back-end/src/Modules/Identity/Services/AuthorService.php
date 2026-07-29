<?php

namespace Modules\Identity\Services;

use Modules\Identity\DTOs\AuthorDTO;
use Modules\Identity\Models\User;
use Modules\Identity\Services\Contracts\AuthorServiceInterface;

class AuthorService implements AuthorServiceInterface
{
    public function findByUsername(string $username): ?AuthorDTO
    {
        $user = User::where('email', $username)->first(); // Using email as username for now; adjust as needed.

        if (! $user) return null;

        return $this->toDTO($user);
    }

    public function listAuthors(): array
    {
        return User::whereHas('roles', fn($q) => $q->where('name', 'user'))
            ->get()
            ->map(fn($u) => $this->toDTO($u))
            ->all();
    }

    protected function toDTO(User $user): AuthorDTO
    {
        return new AuthorDTO(
            id:       $user->id,
            username: $user->email,
            name:     $user->name,
            avatar:   $user->avatar ?? null,
            bio:      $user->bio ?? null,
        );
    }
}