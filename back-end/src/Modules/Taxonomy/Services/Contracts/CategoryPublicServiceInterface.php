<?php

namespace Modules\Taxonomy\Services\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface CategoryPublicServiceInterface
{
    public function getPublicCategories(?string $search = null, int $perPage = 10): LengthAwarePaginator;

    /**
     * Returns the category UUID for a given slug.
     * Throws ModelNotFoundException (mapped to 404) if the slug does not exist.
     */
    public function getCategoryIdBySlug(string $slug): string;

    public function getPopularCategories(int $limit): array;

    public function getCategoryStats(): array;

    /**
     * @param array<string> $ids
     * @return array<string, array{id: string, name: string, slug: string}>
     */
    public function getCategoriesByIds(array $ids): array;

    /**
     * @return array{id: string, name: string, slug: string, posts_count: int, authors_count: int}
     */
    public function getCategoryMetaBySlug(string $slug): array;
}