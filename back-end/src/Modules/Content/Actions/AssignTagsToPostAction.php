<?php

namespace Modules\Content\Actions;

use Modules\Content\Models\Post;
use Modules\Content\Models\Tag;

class AssignTagsToPostAction
{
    /**
     * @param Post $post
     * @param array<int, string|Tag> $tags Array of tag IDs (UUIDs) or Tag model instances
     */
    public function execute(Post $post, array $tags): void
    {
        $tagIds = [];
        foreach ($tags as $tag) {
            if ($tag instanceof Tag) {
                $tagIds[] = $tag->id;
            } elseif (is_string($tag)) {
                $tagIds[] = $tag;
            }
        }
        $post->tags()->sync($tagIds);
    }
}