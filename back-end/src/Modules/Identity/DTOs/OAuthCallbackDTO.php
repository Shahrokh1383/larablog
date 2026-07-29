<?php

namespace Modules\Identity\DTOs;

final readonly class OAuthCallbackDTO
{
    public function __construct(
        public string $provider,
        public string $code,
        public ?string $state = null,
    ) {}
}