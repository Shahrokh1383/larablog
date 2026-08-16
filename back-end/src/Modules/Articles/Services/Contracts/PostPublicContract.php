<?php

namespace Modules\Articles\Services\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface PostPublicContract
{
    public function getPaginatedPosts(int $perPage = 10): LengthAwarePaginator;
    
    public function getBySlug(string $slug): ?object;
    
    public function getRelatedPosts(string $slug, int $limit = 3): array;
    
    // Changed from categorySlug to categoryId to prevent Articles from importing Content models
    public function getPostsByCategory(string $categoryId, ?string $sort = 'newest', int $perPage = 10): LengthAwarePaginator;
    
    // Changed from tagSlug to tagId
    public function getPostsByTag(string $tagId, ?string $sort = 'newest', int $perPage = 10): LengthAwarePaginator;
    
    public function getPostsByAuthor(string $username, ?string $sort = 'newest', int $perPage = 6): LengthAwarePaginator;
    
    public function searchPosts(?string $term, int $perpage = 10): LengthAwarePaginator;

    // New methods required by HomePublicService
    public function getFeaturedPosts(int $limit = 4): Collection;
    public function getRecentPosts(int $limit = 6, array $excludeIds = []): Collection;
    public function getTotalPostsCount(): int;
}