<?php

use Modules\Articles\Models\Post;
use Modules\Articles\Actions\AssignTagsToPostAction;
use Modules\Taxonomy\Models\Tag;
use Illuminate\Support\Facades\DB;

it('assigns tags to post', function () {
    $post = Post::factory()->create();
    $tag1 = Tag::factory()->create();
    $tag2 = Tag::factory()->create();

    app(AssignTagsToPostAction::class)->execute($post, [$tag1->id, $tag2->id]);

    $rows = DB::table('content_post_tag')->where('post_id', $post->id)->get();
    expect($rows)->toHaveCount(2)
        ->and($rows->pluck('tag_id')->toArray())->toContain($tag1->id, $tag2->id);
});

it('removes previous tags before assigning', function () {
    $post = Post::factory()->create();
    $oldTag = Tag::factory()->create();
    $newTag = Tag::factory()->create();

    DB::table('content_post_tag')->insert([
        'post_id' => $post->id,
        'tag_id' => $oldTag->id,
    ]);

    app(AssignTagsToPostAction::class)->execute($post, [$newTag->id]);

    $rows = DB::table('content_post_tag')->where('post_id', $post->id)->get();
    expect($rows)->toHaveCount(1)
        ->and($rows->first()->tag_id)->toBe($newTag->id);
});

it('deduplicates tag ids', function () {
    $post = Post::factory()->create();
    $tag = Tag::factory()->create();

    app(AssignTagsToPostAction::class)->execute($post, [$tag->id, $tag->id]);

    expect(DB::table('content_post_tag')->where('post_id', $post->id)->count())->toBe(1);
});

it('clears tags when empty array given', function () {
    $post = Post::factory()->create();
    $tag = Tag::factory()->create();
    DB::table('content_post_tag')->insert(['post_id' => $post->id, 'tag_id' => $tag->id]);

    app(AssignTagsToPostAction::class)->execute($post, []);

    expect(DB::table('content_post_tag')->where('post_id', $post->id)->count())->toBe(0);
});