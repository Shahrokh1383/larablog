<?php

namespace Modules\Content\Services\Contracts;

interface PostInfoContract
{
    /**
     * @return object{authorId: string, title: string, slug: string}|null
     */
    public function getPostInfo(string $postId): ?object;

    /**
     * @return object{readingTime: int}|null
     */
    public function getPostReadingTime(string $postId): ?object;

    /**
     * Returns a map of post data keyed by post_id.
     * @return array<string, object>
     */
    public function getPostsByIds(array $postIds): array;
}