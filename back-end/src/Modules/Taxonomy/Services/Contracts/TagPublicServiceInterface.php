<?php

namespace Modules\Taxonomy\Services\Contracts;

interface TagPublicServiceInterface
{
    public function getTagIdBySlug(string $slug): string;

    public function getPopularTagsAsArray(int $limit): array;

    public function getTagStats(): array;
}