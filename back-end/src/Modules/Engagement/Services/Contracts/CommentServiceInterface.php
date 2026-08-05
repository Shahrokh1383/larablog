<?php

namespace Modules\Engagement\Services\Contracts;

interface CommentServiceInterface
{
    public function getCommentCountsForPosts(array $postIds): array;
}