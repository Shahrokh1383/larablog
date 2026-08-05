<?php

namespace Modules\Content\Services;

use Modules\Content\Models\Post;
use Modules\Content\Services\Contracts\PostInfoContract;

class PostInfoService implements PostInfoContract
{
    public function getPostInfo(string $postId): ?object
    {
        $post = Post::find($postId);
        if (!$post) return null;
        
        return (object) [
            'authorId' => $post->user_id,
            'title'    => $post->title,
        ];
    }
}