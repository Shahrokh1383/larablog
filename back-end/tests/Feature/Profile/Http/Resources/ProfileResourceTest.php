<?php

use Modules\Profile\Http\Resources\ProfileResource;
use Modules\Profile\Models\Profile;
use Shared\Models\User;

test('profile resource transforms model correctly', function () {
    $user = User::factory()->create(['name' => 'John Doe', 'username' => 'johndoe']);
    $profile = Profile::factory()->create([
        'user_id' => $user->id,
        'avatar' => 'http://example.com/avatar.png',
        'bio' => 'A short bio',
        'expertise' => 'PHP',
        'years_of_experience' => 8,
        'social_links' => ['https://twitter.com/johndoe'],
    ]);
    $profile->load('user');

    $resource = (new ProfileResource($profile))->toArray(request());

    expect($resource)->toHaveKeys([
        'id', 'user_id', 'name', 'username', 'avatar', 'bio',
        'expertise', 'years_of_experience', 'social_links',
        'posts_count', 'total_views', 'created_at', 'updated_at',
    ]);
    expect($resource['name'])->toBe('John Doe');
    expect($resource['username'])->toBe('johndoe');
    expect($resource['avatar'])->toBe('http://example.com/avatar.png');
    expect($resource['bio'])->toBe('A short bio');
    expect($resource['expertise'])->toBe('PHP');
    expect($resource['years_of_experience'])->toBe(8);
    expect($resource['social_links'])->toBeArray();
    expect($resource['social_links'][0])->toBe('https://twitter.com/johndoe');
    expect($resource['posts_count'])->toBe(0);
    expect($resource['total_views'])->toBe(0);
});

test('profile resource handles null fields gracefully', function () {
    $user = User::factory()->create();
    $profile = Profile::factory()->create([
        'user_id' => $user->id,
        'avatar' => null,
        'bio' => null,
        'expertise' => null,
        'years_of_experience' => null,
        'social_links' => null,
    ]);
    $profile->load('user');

    $resource = (new ProfileResource($profile))->toArray(request());

    expect($resource['avatar'])->toBeNull();
    expect($resource['bio'])->toBeNull();
    expect($resource['expertise'])->toBeNull();
    expect($resource['years_of_experience'])->toBeNull();
    expect($resource['social_links'])->toBeArray();
    expect($resource['social_links'])->toBeEmpty();
});