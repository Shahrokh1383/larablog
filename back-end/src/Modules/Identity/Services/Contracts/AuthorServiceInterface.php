<?php

namespace Modules\Identity\Services\Contracts;

use Modules\Identity\DTOs\AuthorDTO;

interface AuthorServiceInterface
{
    public function getByUserId(string|int $userId): ?AuthorDTO;
    /** @return AuthorDTO[] */
    public function getByUserIds(array $userIds): array;
    public function getByUsername(string $username): ?AuthorDTO;
    /** @return AuthorDTO[] */
    public function getAllAuthors(): array;
}