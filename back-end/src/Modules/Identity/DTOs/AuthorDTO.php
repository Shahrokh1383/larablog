<?php

namespace Modules\Identity\DTOs;

final readonly class AuthorDTO
{
    public function __construct(
        public string $id,
        public string $username,
        public string $name,
        public ?string $avatar,
        public ?string $bio,
    ) {}
}