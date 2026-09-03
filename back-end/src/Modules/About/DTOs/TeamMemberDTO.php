<?php

namespace Modules\About\DTOs;

class TeamMemberDTO
{
    public function __construct(
        public readonly ?string $userId,
        public readonly ?int $sortOrder,
        public readonly ?bool $isActive,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            userId: $data['user_id'] ?? null,
            sortOrder: isset($data['sort_order']) ? (int) $data['sort_order'] : null,
            isActive: isset($data['is_active']) ? (bool) $data['is_active'] : null,
        );
    }
}