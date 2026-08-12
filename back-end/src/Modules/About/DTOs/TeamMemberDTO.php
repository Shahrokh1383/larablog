<?php

namespace Modules\About\DTOs;

class TeamMemberDTO
{
    public function __construct(
        public readonly string $userId,
        public readonly ?string $displayName,
        public readonly string $position,
        public readonly ?string $bio,
        public readonly ?string $photo,
        public readonly int $sortOrder,
        public readonly bool $isActive,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            userId: $data['user_id'],
            displayName: $data['display_name'] ?? null,
            position: $data['position'],
            bio: $data['bio'] ?? null,
            photo: $data['photo'] ?? null,
            sortOrder: (int) ($data['sort_order'] ?? 0),
            isActive: (bool) ($data['is_active'] ?? true),
        );
    }
}