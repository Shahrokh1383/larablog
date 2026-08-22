<?php

namespace Modules\Taxonomy\Services\Contracts;

use Illuminate\Database\Eloquent\Builder;

interface TagPublicServiceInterface
{
    /**
     * Returns the tag UUID for a given slug, or null if not found.
     * Callers must handle the null case explicitly.
     */
    public function getTagIdBySlug(string $slug): ?string;

    public function getPopularTagsAsArray(int $limit): array;

    public function getTagStats(): array;

    /**
     * @param array<string> $postIds
     * @return array<string, array<int, array{id: string, name: string, slug: string}>>
     */
    public function getTagsByPostIds(array $postIds): array;

    public function applyTagPostFilter(Builder $query, string $tagId): Builder;
}