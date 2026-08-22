<?php

namespace Modules\Articles\Actions;

use Modules\Articles\Models\Post;
use Illuminate\Support\Facades\DB;

class AssignTagsToPostAction
{
    /**
     * Syncs tags for a post using direct DB operations to strictly avoid 
     * importing the Taxonomy Tag model, enforcing Article III boundaries.
     * 
     * @param Post $post
     * @param array<int, string> $tagIds Array of tag UUIDs
     */
    public function execute(Post $post, array $tagIds): void
    {
        DB::transaction(function () use ($post, $tagIds) {
            DB::table('content_post_tag')
                ->where('post_id', $post->id)
                ->delete();

            if (!empty($tagIds)) {
                $insertData = array_map(fn($tagId) => [
                    'post_id' => $post->id,
                    'tag_id'  => $tagId,
                ], $tagIds);

                DB::table('content_post_tag')->insert($insertData);
            }
        });
    }
}