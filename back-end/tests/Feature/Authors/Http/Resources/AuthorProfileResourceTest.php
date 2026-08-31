<?php

use Modules\Authors\Http\Resources\AuthorProfileResource;
use Modules\Profile\Models\Profile;
use Shared\Models\User;

test('resource transforms profile with full details', function () {
    $user = User::factory()->create(['name' => 'John', 'username' => 'john']);
    $profile = Profile::factory()->create([
        'user_id' => $user->id,
        'avatar' => 'http://example.com/avatar.png',
        'bio' => 'John bio',
        'expertise' => 'Laravel',
        'years_of_experience' => 8,
        'social_links' => ['https://twitter.com/john', 'https://github.com/john'],
    ]);
    $profile->load('user');
    $profile->posts_count = 12;
    $profile->total_views = 700;

    $resource = (new AuthorProfileResource($profile))->toArray(request());

    expect($resource)->toHaveKeys([
        'id', 'name', 'username', 'avatar', 'bio', 'expertise',
        'years_of_experience', 'social_links', 'posts_count', 'total_views',
    ]);
    expect($resource['id'])->toBe($user->id);
    expect($resource['name'])->toBe('John');
    expect($resource['username'])->toBe('john');
    expect($resource['social_links'])->toBeArray();
    expect($resource['social_links'][0])->toBe('https://twitter.com/john');
    expect($resource['posts_count'])->toBe(12);
    expect($resource['total_views'])->toBe(700);
});

test('resource handles null social links as empty array', function () {
    $user = User::factory()->create();
    $profile = Profile::factory()->create(['user_id' => $user->id, 'social_links' => null]);
    $profile->load('user');

    $resource = (new AuthorProfileResource($profile))->toArray(request());

    expect($resource['social_links'])->toBe([]);
});