<?php

namespace Modules\Taxonomy\Services\Contracts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface TagPublicServiceInterface
{
    public function getPublicTags(?string $search = null, int $perPage = 12): LengthAwarePaginator;

    public function getPopularTags(int $limit = 10): array;

    /**
     * @param array<string> $postIds
     * @return array<string, array<int, array{id: string, name: string, slug: string}>>
     */
    public function getTagsByPostIds(array $postIds): array;

    public function applyTagPostFilter(Builder $query, string $tagId): Builder;

    /**
     * @return array{id: string, name: string, slug: string, posts_count: int}
     */
    public function getTagMetaBySlug(string $slug): array;
}