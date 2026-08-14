<?php

namespace Modules\Identity\DTOs;

final readonly class UserRegisterDTO
{
    public function __construct(
        public string $name,
        public string $email,
        public ?string $password,
    ) {}
}