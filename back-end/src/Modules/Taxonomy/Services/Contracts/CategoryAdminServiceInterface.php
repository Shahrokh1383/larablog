<?php

namespace Modules\Taxonomy\Services\Contracts;

interface CategoryAdminServiceInterface
{
    /**
     * Get categories by IDs. Returns an array of arrays to avoid leaking Eloquent Models across boundaries.
     * 
     * @param array<int|string> $ids
     * @return array<string, array{id: string, name: string, slug: string}> Keyed by ID
     */
    public function getByIds(array $ids): array;
}