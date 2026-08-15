<?php

namespace Modules\Articles\Actions;

use Modules\Articles\Models\Post;

class AssignTagsToPostAction
{
    /**
     * @param Post $post
     * @param array<int, string> $tagIds Array of tag UUIDs
     */
    public function execute(Post $post, array $tagIds): void
    {
        $post->tags()->sync($tagIds);
    }
}