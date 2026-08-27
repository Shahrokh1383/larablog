<?php

use Modules\Identity\Models\User;
use Modules\Articles\Models\Post;
use Modules\Articles\Services\PostStatsService;
use Modules\Taxonomy\Models\Category;
use Modules\Taxonomy\Models\Tag;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    $this->service = app(PostStatsService::class);
});

it('returns total and published posts counts', function () {
    Post::factory()->count(2)->create(['is_published' => true]);
    Post::factory()->create(['is_published' => false]);

    expect($this->service->getTotalPostsCount())->toBe(3)
        ->and($this->service->getPublishedPostsCount())->toBe(2);
});

it('returns total views', function () {
    Post::factory()->create(['views' => 10]);
    Post::factory()->create(['views' => 20]);
    Post::factory()->create(['views' => 30]);

    expect($this->service->getTotalViews())->toBe(60);
});

it('returns author stats', function () {
    $author1 = User::factory()->create();
    $author2 = User::factory()->create();

    Post::factory()->count(2)->create(['user_id' => $author1->id, 'views' => 15]);
    Post::factory()->create(['user_id' => $author2->id, 'views' => 7]);

    $stats = $this->service->getAuthorStats();

    expect($stats)->toHaveKeys([$author1->id, $author2->id])
        ->and($stats[$author1->id]['posts_count'])->toBe(2)
        ->and($stats[$author1->id]['total_views'])->toBe(30);
});

it('returns published post counts by categories', function () {
    $cat1 = Category::factory()->create();
    $cat2 = Category::factory()->create();

    Post::factory()->count(2)->create(['category_id' => $cat1->id, 'is_published' => true]);
    Post::factory()->create(['category_id' => $cat1->id, 'is_published' => false]);
    Post::factory()->create(['category_id' => $cat2->id, 'is_published' => true]);

    $result = $this->service->getPublishedPostCountsByCategories([$cat1->id, $cat2->id]);

    expect($result)->toMatchArray([
        $cat1->id => 2,
        $cat2->id => 1,
    ]);
});

it('returns distinct author counts by categories', function () {
    $cat = Category::factory()->create();
    $author1 = User::factory()->create();
    $author2 = User::factory()->create();

    Post::factory()->create(['category_id' => $cat->id, 'user_id' => $author1->id, 'is_published' => true]);
    Post::factory()->create(['category_id' => $cat->id, 'user_id' => $author1->id, 'is_published' => true]);
    Post::factory()->create(['category_id' => $cat->id, 'user_id' => $author2->id, 'is_published' => true]);

    $result = $this->service->getDistinctAuthorCountsByCategories([$cat->id]);

    expect($result)->toMatchArray([$cat->id => 2]);
});

it('returns popular category stats ordered by count', function () {
    $cat1 = Category::factory()->create();
    $cat2 = Category::factory()->create();

    Post::factory()->count(3)->create(['category_id' => $cat1->id, 'is_published' => true]);
    Post::factory()->count(1)->create(['category_id' => $cat2->id, 'is_published' => true]);

    $result = $this->service->getPopularCategoryStats(10);

    expect($result)->toHaveCount(2)
        ->and($result[0]['category_id'])->toBe($cat1->id)
        ->and($result[0]['posts_count'])->toBe(3);
});

it('returns published post counts by tags', function () {
    $tag = Tag::factory()->create();
    $post1 = Post::factory()->create(['is_published' => true]);
    $post2 = Post::factory()->create(['is_published' => true]);
    $post3 = Post::factory()->create(['is_published' => false]);

    DB::table('content_post_tag')->insert([
        ['post_id' => $post1->id, 'tag_id' => $tag->id],
        ['post_id' => $post2->id, 'tag_id' => $tag->id],
        ['post_id' => $post3->id, 'tag_id' => $tag->id],
    ]);

    $result = $this->service->getPublishedPostCountsByTags([$tag->id]);

    expect($result)->toMatchArray([$tag->id => 2]);
});

it('returns published post views sum by tags', function () {
    $tag = Tag::factory()->create();
    $post1 = Post::factory()->create(['is_published' => true, 'views' => 40]);
    $post2 = Post::factory()->create(['is_published' => true, 'views' => 60]);

    DB::table('content_post_tag')->insert([
        ['post_id' => $post1->id, 'tag_id' => $tag->id],
        ['post_id' => $post2->id, 'tag_id' => $tag->id],
    ]);

    $result = $this->service->getPublishedPostViewsSumByTags([$tag->id]);

    expect($result)->toMatchArray([$tag->id => 100]);
});

it('returns popular tag stats ordered by total views', function () {
    $tag1 = Tag::factory()->create();
    $tag2 = Tag::factory()->create();
    $post1 = Post::factory()->create(['is_published' => true, 'views' => 10]);
    $post2 = Post::factory()->create(['is_published' => true, 'views' => 50]);

    DB::table('content_post_tag')->insert([
        ['post_id' => $post1->id, 'tag_id' => $tag1->id],
        ['post_id' => $post2->id, 'tag_id' => $tag2->id],
    ]);

    $result = $this->service->getPopularTagStats(10);

    expect($result)->toHaveCount(2)
        ->and($result[0]['tag_id'])->toBe($tag2->id)
        ->and($result[0]['total_views'])->toBe(50);
});