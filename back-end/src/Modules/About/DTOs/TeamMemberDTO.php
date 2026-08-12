<?php

namespace Modules\About\DTOs;

class TeamMemberDTO
{
    public function __construct(
        public readonly string $userId,
        public readonly int $sortOrder,
        public readonly bool $isActive,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            userId: $data['user_id'],
            sortOrder: (int) ($data['sort_order'] ?? 0),
            isActive: (bool) ($data['is_active'] ?? true),
        );
    }
}