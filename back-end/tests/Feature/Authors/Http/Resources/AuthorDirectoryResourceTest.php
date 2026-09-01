<?php

use Modules\Authors\Http\Resources\AuthorDirectoryResource;
use Modules\Profile\Models\Profile;
use Shared\Models\User;

test('resource transforms profile for directory with stats', function () {
    $user = User::factory()->create(['name' => 'Alice', 'username' => 'alice']);
    $profile = Profile::factory()->create([
        'user_id' => $user->id,
        'avatar' => 'http://example.com/avatar.png',
        'bio' => 'Alice bio',
        'expertise' => 'PHP',
    ]);
    $profile->load('user');
    $profile->posts_count = 10;
    $profile->total_views = 500;

    $resource = (new AuthorDirectoryResource($profile))->toArray(request());

    expect($resource)->toHaveKeys([
        'id', 'name', 'username', 'avatar', 'bio', 'expertise', 'posts_count', 'total_views',
    ]);
    expect($resource['id'])->toBe($user->id);
    expect($resource['name'])->toBe('Alice');
    expect($resource['username'])->toBe('alice');
    expect($resource['avatar'])->toBe('http://example.com/avatar.png');
    expect($resource['bio'])->toBe('Alice bio');
    expect($resource['expertise'])->toBe('PHP');
    expect($resource['posts_count'])->toBe(10);
    expect($resource['total_views'])->toBe(500);
});

test('resource returns zero stats when not set', function () {
    $user = User::factory()->create();
    $profile = Profile::factory()->create(['user_id' => $user->id]);
    $profile->load('user');

    $resource = (new AuthorDirectoryResource($profile))->toArray(request());

    expect($resource['posts_count'])->toBe(0);
    expect($resource['total_views'])->toBe(0);
});