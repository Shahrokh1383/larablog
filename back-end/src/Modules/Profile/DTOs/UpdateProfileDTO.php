<?php

namespace Modules\Profile\DTOs;

final class UpdateProfileDTO
{
    public function __construct(
        public readonly string $name, // Handled via Identity contract
        public readonly ?string $avatar = null,
        public readonly ?string $bio = null,
        public readonly ?string $expertise = null,
        public readonly ?int $years_of_experience = null,
        public readonly ?array $social_links = null,
    ) {}
}