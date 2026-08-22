<?php

namespace Modules\Taxonomy\Services\Contracts;

interface TagPublicServiceInterface
{
    public function getTagIdBySlug(string $slug): string;
    public function getPopularTagsAsArray(int $limit): array;
    public function getTagStats(): array;
    
    /**
     * @param array<string> $postIds
     * @return array<string, array<int, array{id: string, name: string, slug: string}>>
     */
    public function getTagsByPostIds(array $postIds): array;

    /**
     * @return array<string> Array of Post UUIDs
     */
    public function getPostIdsByTag(string $tagId): array;
}