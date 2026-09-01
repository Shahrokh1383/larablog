<?php

use Modules\Profile\Http\Resources\AuthorResource;
use Modules\Profile\Models\Profile;
use Shared\Models\User;

test('author resource transforms profile with user data', function () {
    $user = User::factory()->create(['name' => 'Jane Author', 'username' => 'jane']);
    $profile = Profile::factory()->create([
        'user_id' => $user->id,
        'avatar' => 'http://example.com/avatar.png',
        'bio' => 'Author bio',
        'expertise' => 'Writing',
        'social_links' => ['https://twitter.com/jane'],
    ]);
    $profile->load('user');

    // Simulate posts_count and total_views
    $profile->setAttribute('posts_count', 5);
    $profile->setAttribute('total_views', 1000);

    $resource = (new AuthorResource($profile))->toArray(request());

    expect($resource)->toHaveKeys([
        'id', 'name', 'username', 'avatar', 'bio',
        'expertise', 'social_links', 'posts_count', 'total_views',
    ]);
    expect($resource['name'])->toBe('Jane Author');
    expect($resource['username'])->toBe('jane');
    expect($resource['avatar'])->toBe('http://example.com/avatar.png');
    expect($resource['bio'])->toBe('Author bio');
    expect($resource['expertise'])->toBe('Writing');
    expect($resource['social_links'])->toBeArray();
    expect($resource['posts_count'])->toBe(5);
    expect($resource['total_views'])->toBe(1000);
});