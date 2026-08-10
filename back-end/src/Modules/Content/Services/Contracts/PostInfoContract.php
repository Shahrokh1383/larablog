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

    /**
     * Get top published posts of the current week based on views.
     * 
     * @return array<int, object{id: string, title: string, slug: string, excerpt: ?string, views: int}>
     */
    public function getTopPostsOfWeek(int $limit = 5): array;
}