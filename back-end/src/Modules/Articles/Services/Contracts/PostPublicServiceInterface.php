<?php

namespace Modules\Articles\Services\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface PostPublicServiceInterface
{
    public function getPaginatedPosts(int $perPage = 10): LengthAwarePaginator;

    public function getBySlug(string $slug): ?object;

    public function getRelatedPosts(string $slug, int $limit = 3): array;

    public function getPostsByAuthor(string $username, ?string $sort = 'newest', int $perPage = 6): LengthAwarePaginator;

    public function searchPosts(?string $term, int $perpage = 10): LengthAwarePaginator;

    public function getFeaturedPosts(int $limit = 4): Collection;

    public function getRecentPosts(int $limit = 6, array $excludeIds = []): Collection;

    /**
     * @return array{
     *     category: array{id: string, name: string, slug: string, posts_count: int, authors_count: int},
     *     posts: LengthAwarePaginator
     * }
     */
    public function getPublishedPostsByCategoryForPublic(
        string $categorySlug,
        ?string $sort = 'newest',
        int $perPage = 10
    ): array;

    /**
     * @return array{
     *     tag: array{id: string, name: string, slug: string, posts_count: int},
     *     posts: LengthAwarePaginator
     * }
     */
    public function getPublishedPostsByTagForPublic(
        string $tagSlug,
        ?string $sort = 'newest',
        int $perPage = 10
    ): array;

    /**
     * Total number of currently published posts.
     */
    public function getPublishedPostsCount(): int;
}