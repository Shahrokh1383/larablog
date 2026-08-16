<?php

namespace Modules\Taxonomy\Services\Contracts;

interface CategoryPublicServiceInterface
{
    public function getCategoryIdBySlug(string $slug): string;

    public function getPopularCategories(int $limit): array;

    public function getCategoryStats(): array;
}