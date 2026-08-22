<?php

use Modules\Taxonomy\Models\Tag;
use Modules\Taxonomy\Services\TagPublicService;
use Modules\Articles\Services\Contracts\PostPublicServiceInterface;
use Mockery\MockInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;

it('returns public tags with post counts', function () {
    $tag1 = Tag::factory()->create();
    $tag2 = Tag::factory()->create();

    $this->mock(PostPublicServiceInterface::class, function (MockInterface $mock) use ($tag1, $tag2) {
        $mock->shouldReceive('getPublishedPostCountsByTags')
            ->once()
            ->withArgs(fn ($ids) => count($ids) === 2)
            ->andReturn([
                $tag1->id => 5,
                $tag2->id => 10,
            ]);
    });

    $service = app(TagPublicService::class);
    $paginator = $service->getPublicTags(null, 12);

    expect($paginator->total())->toBe(2);
    $items = $paginator->items();
    expect($items[0]->posts_count)->toBeIn([5, 10]);
});

it('returns public tag by slug with posts count', function () {
    $tag = Tag::factory()->create(['slug' => 'known-tag']);

    $this->mock(PostPublicServiceInterface::class, function (MockInterface $mock) use ($tag) {
        $mock->shouldReceive('getPublishedPostCountsByTags')
            ->once()
            ->with([$tag->id])
            ->andReturn([$tag->id => 7]);
    });

    $service = app(TagPublicService::class);
    $result = $service->getPublicTagBySlug('known-tag');

    expect($result->id)->toBe($tag->id)
        ->and($result->posts_count)->toBe(7);
});

it('throws ModelNotFoundException for missing tag slug', function () {
    $this->mock(PostPublicServiceInterface::class);

    $service = app(TagPublicService::class);

    expect(fn () => $service->getTagIdBySlug('missing'))->toThrow(ModelNotFoundException::class);
});

it('returns popular tags preserving order', function () {
    $tag1 = Tag::factory()->create();
    $tag2 = Tag::factory()->create();

    $stats = [
        ['tag_id' => $tag2->id, 'posts_count' => 20, 'total_views' => 200],
        ['tag_id' => $tag1->id, 'posts_count' => 10, 'total_views' => 100],
    ];

    $this->mock(PostPublicServiceInterface::class, function (MockInterface $mock) use ($stats) {
        $mock->shouldReceive('getPopularTagStats')
            ->once()
            ->with(5)
            ->andReturn($stats);
    });

    $service = app(TagPublicService::class);
    $result = $service->getPopularTags(5);

    expect($result)->toHaveCount(2)
        ->and($result[0]['id'])->toBe($tag2->id)
        ->and($result[0]['posts_count'])->toBe(20)
        ->and($result[0]['total_views'])->toBe(200)
        ->and($result[1]['id'])->toBe($tag1->id);
});

it('returns tag stats', function () {
    Tag::factory()->count(3)->create();

    $this->mock(PostPublicServiceInterface::class);

    $service = app(TagPublicService::class);
    $stats = $service->getTagStats();

    expect($stats['total_tags'])->toBe(3);
});