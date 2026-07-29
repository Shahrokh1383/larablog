<?php

namespace Modules\Identity\Services\Contracts;

use Modules\Identity\DTOs\AuthorDTO;

interface AuthorServiceInterface
{
    public function findByUsername(string $username): ?AuthorDTO;
    public function listAuthors(): array;
}