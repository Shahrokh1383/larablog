<?php

namespace Modules\Identity\DTOs;

final readonly class ResetPasswordDTO
{
    public function __construct(
        public string $token,
        public string $email,
        public string $password,
    ) {}
}