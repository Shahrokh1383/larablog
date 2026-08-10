<?php

namespace Modules\Marketing\DTOs;

final class SubscribeDTO
{
    public function __construct(
        public readonly string $email,
    ) {}
}