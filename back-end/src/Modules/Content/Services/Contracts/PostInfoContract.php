<?php

namespace Modules\Content\Services\Contracts;

interface PostInfoContract
{
    /**
     * @return object{authorId: string, title: string, slug: string}|null
     */
    public function getPostInfo(string $postId): ?object;
}