<?php

namespace Modules\Articles\Services\Contracts;

interface PostInfoContract
{
    public function getPostInfo(string $postId): ?object;
    public function getPostReadingTime(string $postId): ?object;
    public function getPostsByIds(array $postIds): array;
    
    /**
     * @param array|\Closure|\Illuminate\Database\Eloquent\Builder $postIds
     */
    public function getTotalReadingTimeByIds($postIds): int;

    /**
     * @return array<int, object{id: string, title: string, slug: string, excerpt: ?string, views: int}>
     */
    public function getTopPostsOfWeek(int $limit = 5): array;
}