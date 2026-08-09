<?php

namespace Modules\Content\Services\Contracts;

interface PostInfoContract
{
    public function getPostInfo(string $postId): ?object;
    public function getPostReadingTime(string $postId): ?object;
    public function getPostsByIds(array $postIds): array;
    
    /**
     * Get the sum of reading times for a given array of post IDs at the DB level.
     */
    public function getTotalReadingTimeByIds(array $postIds): int;
}