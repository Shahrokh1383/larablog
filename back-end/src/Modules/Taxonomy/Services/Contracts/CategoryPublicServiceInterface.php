<?php

namespace Modules\Taxonomy\Services\Contracts;

interface CategoryPublicServiceInterface
{
    /**
     * Returns the category UUID for a given slug, or null if not found.
     * Callers must handle the null case explicitly.
     */
    public function getCategoryIdBySlug(string $slug): ?string;

    public function getPopularCategories(int $limit): array;

    public function getCategoryStats(): array;

    /**
     * @param array<string> $ids
     * @return array<string, array{id: string, name: string, slug: string}>
     */
    public function getCategoriesByIds(array $ids): array;
}