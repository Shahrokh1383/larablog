<?php

namespace Modules\Content\DTOs;

final class PostUpdateDTO
{
    public function __construct(
        public readonly ?string $title = null,
        public readonly ?string $body = null,
        public readonly ?string $excerpt = null,
        public readonly ?string $featuredImage = null,
        public readonly ?bool $isPublished = null,
        public readonly ?string $publishedAt = null,
        public readonly ?string $categoryId = null,
        public readonly ?array $tagIds = null,
    ) {}
}