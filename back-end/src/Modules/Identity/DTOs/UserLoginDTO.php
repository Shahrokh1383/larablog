<?php

namespace Modules\Identity\DTOs;

final readonly class UserLoginDTO
{
    public function __construct(
        public string $email,
        public string $password,
        public bool $remember = false,
    ) {}
}