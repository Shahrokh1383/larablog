<?php

use Modules\Identity\Models\User;
use Modules\Articles\Models\Post;
use Modules\Articles\Services\PostAdminStatsService;
use Modules\Taxonomy\Models\Category;
use Modules\Taxonomy\Models\Tag;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    $this->service = app(PostAdminStatsService::class);
});

it('returns total and published counts including drafts', function () {
    Post::factory()->count(2)->create(['is_published' => true]);
    Post::factory()->create(['is_published' => false]);

    expect($this->service->getTotalPostsCount())->toBe(3)
        ->and($this->service->getPublishedPostsCount())->toBe(2);
});

it('returns total views', function () {
    Post::factory()->create(['views' => 5]);
    Post::factory()->create(['views' => 12]);

    expect($this->service->getTotalViews())->toBe(17);
});

it('returns author stats including unpublished', function () {
    $author = User::factory()->create();
    Post::factory()->count(2)->create(['user_id' => $author->id, 'is_published' => false, 'views' => 10]);

    $stats = $this->service->getAuthorStats();

    expect($stats[$author->id]['posts_count'])->toBe(2)
        ->and($stats[$author->id]['total_views'])->toBe(20);
});

it('returns total post counts by categories including drafts', function () {
    $cat = Category::factory()->create();
    Post::factory()->count(2)->create(['category_id' => $cat->id, 'is_published' => false]);
    Post::factory()->create(['category_id' => $cat->id, 'is_published' => true]);

    $result = $this->service->getTotalPostCountsByCategories([$cat->id]);

    expect($result)->toMatchArray([$cat->id => 3]);
});

it('returns total post counts by tags including drafts', function () {
    $tag = Tag::factory()->create();
    $post1 = Post::factory()->create(['is_published' => false]);
    $post2 = Post::factory()->create(['is_published' => true]);
    $post3 = Post::factory()->create(['is_published' => false]);

    DB::table('content_post_tag')->insert([
        ['post_id' => $post1->id, 'tag_id' => $tag->id],
        ['post_id' => $post2->id, 'tag_id' => $tag->id],
        ['post_id' => $post3->id, 'tag_id' => $tag->id],
    ]);

    $result = $this->service->getTotalPostCountsByTags([$tag->id]);

    expect($result)->toMatchArray([$tag->id => 3]);
});

it('returns popular category and tag stats', function () {
    $cat1 = Category::factory()->create();
    $cat2 = Category::factory()->create();
    $tag1 = Tag::factory()->create();
    $tag2 = Tag::factory()->create();

    $post1 = Post::factory()->create(['category_id' => $cat1->id]);
    $post2 = Post::factory()->create(['category_id' => $cat1->id]);
    $post3 = Post::factory()->create(['category_id' => $cat2->id]);

    DB::table('content_post_tag')->insert([
        ['post_id' => $post1->id, 'tag_id' => $tag1->id],
        ['post_id' => $post2->id, 'tag_id' => $tag1->id],
        ['post_id' => $post3->id, 'tag_id' => $tag2->id],
    ]);

    $catStats = $this->service->getPopularCategoryStats(10);
    $tagStats = $this->service->getPopularTagStats(10);

    expect($catStats[0]['category_id'])->toBe($cat1->id)
        ->and($catStats[0]['posts_count'])->toBe(2)
        ->and($tagStats[0]['tag_id'])->toBe($tag1->id)
        ->and($tagStats[0]['posts_count'])->toBe(2);
});