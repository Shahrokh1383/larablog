<?php

namespace Modules\Marketing\DTOs;

final class ContactMessageDTO
{
    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly string $subject,
        public readonly string $message,
        public readonly ?string $userId = null,
    ) {}
}