<?php

namespace Modules\Taxonomy\Services\Contracts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface TagPublicServiceInterface
{
    public function getPublicTags(?string $search = null, int $perPage = 12): LengthAwarePaginator;

    public function getPopularTags(int $limit = 10): array;

    /**
     * Returns the tag UUID for a given slug.
     * Throws ModelNotFoundException (mapped to 404) if the slug does not exist.
     */
    public function getTagIdBySlug(string $slug): string;

    public function getPopularTagsAsArray(int $limit): array;

    public function getTagStats(): array;

    /**
     * @param array<string> $postIds
     * @return array<string, array<int, array{id: string, name: string, slug: string}>>
     */
    public function getTagsByPostIds(array $postIds): array;

    public function applyTagPostFilter(Builder $query, string $tagId): Builder;
}