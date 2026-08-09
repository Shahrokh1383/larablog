<?php

namespace Modules\Content\Services\Contracts;

interface PostInfoContract
{
    public function getPostInfo(string $postId): ?object;
    public function getPostReadingTime(string $postId): ?object;
    public function getPostsByIds(array $postIds): array;
    
    /**
     * Get the sum of reading times using an array, Closure, or Builder.
     * @param array|\Closure|\Illuminate\Database\Eloquent\Builder $postIds
     */
    public function getTotalReadingTimeByIds($postIds): int;
}