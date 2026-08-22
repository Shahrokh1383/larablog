<?php

use Modules\Taxonomy\Http\Resources\CategoryResource;
use Modules\Taxonomy\Http\Resources\CategoryPublicResource;
use Modules\Taxonomy\Models\Category;
use Illuminate\Http\Request;

test('CategoryResource output shape', function () {
    $category = new Category([
        'id' => 'uuid-1',
        'name' => 'Test Category',
        'slug' => 'test-category',
        'created_at' => '2026-01-01 00:00:00',
        'updated_at' => '2026-01-01 00:00:00',
    ]);
    $category->posts_count = '7';

    $resource = (new CategoryResource($category))->toArray(new Request());

    expect($resource)->toBe([
        'id' => 'uuid-1',
        'name' => 'Test Category',
        'slug' => 'test-category',
        'posts_count' => 7,
        'created_at' => '2026-01-01 00:00:00',
        'updated_at' => '2026-01-01 00:00:00',
    ]);
});

test('CategoryPublicResource includes aggregates when present', function () {
    $category = new Category([
        'id' => 'uuid-2',
        'name' => 'Public Category',
        'slug' => 'public-category',
    ]);
    $category->posts_count = 5;
    $category->authors_count = 2;

    $resource = (new CategoryPublicResource($category))->toArray(new Request());

    expect($resource)->toHaveKeys(['id', 'name', 'slug', 'posts_count', 'authors_count'])
        ->and($resource['posts_count'])->toBe(5)
        ->and($resource['authors_count'])->toBe(2);
});

test('CategoryPublicResource omits aggregates when null', function () {
    $category = new Category([
        'id' => 'uuid-3',
        'name' => 'No Aggregates',
        'slug' => 'no-aggregates',
    ]);

    $resource = (new CategoryPublicResource($category))->toArray(new Request());

    expect($resource)->not->toHaveKeys(['posts_count', 'authors_count'])
        ->and($resource)->toHaveKeys(['id', 'name', 'slug']);
});