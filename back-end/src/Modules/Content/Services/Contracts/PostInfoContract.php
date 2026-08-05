<?php

namespace Modules\Content\Services\Contracts;

interface PostInfoContract
{
    /**
     * @return object{authorId: string, title: string}|null
     */
    public function getPostInfo(string $postId): ?object;
}