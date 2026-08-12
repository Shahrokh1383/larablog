<?php

namespace Modules\About\DTOs;

class SiteSettingsDTO
{
    public function __construct(
        public readonly ?string $callUsPhone,
        public readonly array $callUsEmails,
        public readonly ?string $visitAddress,
        public readonly array $socialLinks,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            callUsPhone: $data['call_us_phone'] ?? null,
            callUsEmails: $data['call_us_emails'] ?? [],
            visitAddress: $data['visit_address'] ?? null,
            socialLinks: $data['social_links'] ?? [],
        );
    }
}