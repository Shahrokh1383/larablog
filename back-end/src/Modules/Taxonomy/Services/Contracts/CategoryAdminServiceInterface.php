<?php

namespace Modules\Taxonomy\Services\Contracts;

use Modules\Taxonomy\Models\Category;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface CategoryAdminServiceInterface
{
    public function getAll(int $perPage = 15, int $page = 1): LengthAwarePaginator;
    public function create(string $name): Category;
    public function getWithStats(Category $category): Category;
    public function update(Category $category, string $name): Category;
    public function delete(Category $category): void;
    
    /**
     * @param array<int|string> $ids
     * @return array<string, array{id: string, name: string, slug: string}>
     */
    public function getByIds(array $ids): array;
}