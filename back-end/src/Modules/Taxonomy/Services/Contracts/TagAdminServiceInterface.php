<?php

namespace Modules\Taxonomy\Services\Contracts;

use Modules\Taxonomy\Models\Tag;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface TagAdminServiceInterface
{
    public function getAll(int $perPage = 15, int $page = 1): LengthAwarePaginator;
    public function create(string $name): Tag;
    public function getWithStats(Tag $tag): Tag;
    public function update(Tag $tag, string $name): Tag;
    public function delete(Tag $tag): void;
    
    /**
     * @param array<int|string> $ids
     * @return array<string, array{id: string, name: string, slug: string}>
     */
    public function getByIds(array $ids): array;
}