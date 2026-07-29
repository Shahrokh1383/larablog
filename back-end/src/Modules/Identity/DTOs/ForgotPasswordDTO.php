<?php

namespace Modules\Identity\DTOs;

final readonly class ForgotPasswordDTO
{
    public function __construct(public string $email) {}
}