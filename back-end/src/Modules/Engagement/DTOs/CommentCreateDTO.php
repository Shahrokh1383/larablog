<?php

namespace Modules\Engagement\DTOs;

final class CommentCreateDTO
{
    public function __construct(
        public readonly string $postId,
        public readonly string $body,
        public readonly ?string $parentId = null,
        public readonly ?string $userId = null,
        public readonly ?string $name = null,
        public readonly ?string $email = null,
    ) {}
}