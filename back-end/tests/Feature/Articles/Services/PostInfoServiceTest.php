<?php

use Modules\Articles\Models\Post;
use Modules\Articles\Services\PostInfoService;
use Carbon\Carbon;

beforeEach(function () {
    $this->service = app(PostInfoService::class);
});

it('returns post info by id', function () {
    $post = Post::factory()->create();

    $info = $this->service->getPostInfo($post->id);

    expect($info)->toBeObject()
        ->and($info->authorId)->toBe($post->user_id)
        ->and($info->title)->toBe($post->title)
        ->and($info->slug)->toBe($post->slug);
});

it('returns null when post info id missing', function () {
    expect($this->service->getPostInfo('missing-id'))->toBeNull();
});

it('returns reading time by id', function () {
    $post = Post::factory()->create(['reading_time' => 8]);

    $result = $this->service->getPostReadingTime($post->id);

    expect($result->readingTime)->toBe(8);
});

it('returns null when reading time post missing', function () {
    expect($this->service->getPostReadingTime('missing-id'))->toBeNull();
});

it('returns posts map by ids', function () {
    $post1 = Post::factory()->create();
    $post2 = Post::factory()->create();

    $result = $this->service->getPostsByIds([$post1->id, $post2->id]);

    expect($result)->toHaveKeys([$post1->id, $post2->id])
        ->and($result[$post1->id])->toHaveProperty('slug', $post1->slug);
});

it('returns empty array for empty posts by ids', function () {
    expect($this->service->getPostsByIds([]))->toBe([]);
});

it('sums reading time by ids', function () {
    $post1 = Post::factory()->create(['reading_time' => 3]);
    $post2 = Post::factory()->create(['reading_time' => 5]);

    expect($this->service->getTotalReadingTimeByIds([$post1->id, $post2->id]))->toBe(8);
});

it('returns zero total reading time for empty ids', function () {
    expect($this->service->getTotalReadingTimeByIds([]))->toBe(0);
});

it('returns top posts of week ordered by views', function () {
    $old = Post::factory()->create(['is_published' => true, 'published_at' => Carbon::now()->subDays(8), 'views' => 100]);
    $top = Post::factory()->create(['is_published' => true, 'published_at' => Carbon::now()->subDay(), 'views' => 50]);
    $second = Post::factory()->create(['is_published' => true, 'published_at' => Carbon::now()->subDay(), 'views' => 40]);

    $result = $this->service->getTopPostsOfWeek(5);

    expect($result)->toHaveCount(2)
        ->and($result[0]->id)->toBe($top->id)
        ->and($result[1]->id)->toBe($second->id);
});