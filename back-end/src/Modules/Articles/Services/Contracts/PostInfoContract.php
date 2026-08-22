<?php

namespace Modules\Articles\Services\Contracts;

interface PostInfoContract
{
    public function getPostInfo(string $postId): ?object;
    public function getPostReadingTime(string $postId): ?object;
    
    /**
     * @param array<string> $postIds
     */
    public function getPostsByIds(array $postIds): array;
    
    /**
     * @param array<string> $postIds Array of Post UUIDs.
     * 
     * Note: Previous contract incorrectly documented support for \Closure and Eloquent Builder.
     * Supporting those types introduces unnecessary complexity, violates KISS, and constitutes
     * over-engineering for a method that strictly aggregates reading time by a known set of IDs.
     * Callers must resolve their builders/closures into a plain array before invoking this method.
     */
    public function getTotalReadingTimeByIds(array $postIds): int;

    /**
     * @return array<int, object{id: string, title: string, slug: string, excerpt: ?string, views: int}>
     */
    public function getTopPostsOfWeek(int $limit = 5): array;
}