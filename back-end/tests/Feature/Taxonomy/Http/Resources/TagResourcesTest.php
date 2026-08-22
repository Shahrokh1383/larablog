<?php

use Modules\Taxonomy\Http\Resources\TagResource;
use Modules\Taxonomy\Http\Resources\TagPublicResource;
use Modules\Taxonomy\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

test('TagResource output shape', function () {
    $tag = new Tag();
    $tag->setRawAttributes([
        'id' => 'uuid-4',
        'name' => 'Test Tag',
        'slug' => 'test-tag',
        'created_at' => '2026-01-01 00:00:00',
        'updated_at' => '2026-01-01 00:00:00',
    ]);
    $tag->posts_count = '3';

    $resource = (new TagResource($tag))->toArray(new Request());

    expect($resource)->toHaveKeys(['id', 'name', 'slug', 'posts_count', 'created_at', 'updated_at'])
        ->and($resource['id'])->toBe('uuid-4')
        ->and($resource['name'])->toBe('Test Tag')
        ->and($resource['slug'])->toBe('test-tag')
        ->and($resource['posts_count'])->toBe(3)
        ->and($resource['created_at'])->toBeInstanceOf(Carbon::class)
        ->and($resource['updated_at'])->toBeInstanceOf(Carbon::class);
});

test('TagPublicResource includes aggregates when present', function () {
    $tag = new Tag();
    $tag->setRawAttributes([
        'id' => 'uuid-5',
        'name' => 'Public Tag',
        'slug' => 'public-tag',
    ]);
    $tag->posts_count = 8;
    $tag->total_views = 100;

    $resource = (new TagPublicResource($tag))->resolve();

    expect($resource)->toHaveKeys(['id', 'name', 'slug', 'posts_count', 'total_views'])
        ->and($resource['posts_count'])->toBe(8)
        ->and($resource['total_views'])->toBe(100);
});

test('TagPublicResource omits aggregates when null', function () {
    $tag = new Tag();
    $tag->setRawAttributes([
        'id' => 'uuid-6',
        'name' => 'No Aggregates',
        'slug' => 'no-aggregates',
    ]);

    $resource = (new TagPublicResource($tag))->resolve();

    expect($resource)->not->toHaveKeys(['posts_count', 'total_views'])
        ->and($resource)->toHaveKeys(['id', 'name', 'slug']);
});