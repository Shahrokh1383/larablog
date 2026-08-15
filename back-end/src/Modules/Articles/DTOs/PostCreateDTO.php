<?php

namespace Modules\Articles\DTOs;

final class PostCreateDTO
{
    public function __construct(
        public readonly string $title,
        public readonly string $body,
        public readonly string $userId,
        public readonly ?string $excerpt = null,
        public readonly ?string $featuredImage = null,
        public readonly bool $isPublished = false,
        public readonly ?string $publishedAt = null,
        public readonly ?string $categoryId = null,
        public readonly array $tagIds = [],
    ) {}
}