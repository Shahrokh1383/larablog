<?php

use Modules\Profile\Http\Requests\UpdateProfileRequest;
use Shared\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('authorize returns true', function () {
    $request = new UpdateProfileRequest();
    expect($request->authorize())->toBeTrue();
});

test('rules contain all expected validation constraints', function () {
    $request = new UpdateProfileRequest();
    $request->setUserResolver(fn () => $this->user);

    $rules = $request->rules();

    expect($rules)->toHaveKeys([
        'name', 'avatar', 'bio', 'expertise',
        'years_of_experience', 'social_links', 'social_links.*',
    ]);

    expect($rules['name'])->toContain('sometimes', 'string', 'max:255');
    expect($rules['avatar'])->toContain('nullable', 'url', 'max:255');
    expect($rules['bio'])->toContain('nullable', 'string', 'max:1000');
    expect($rules['expertise'])->toContain('nullable', 'string', 'max:255');
    expect($rules['years_of_experience'])->toContain('nullable', 'integer', 'min:0', 'max:100');
    expect($rules['social_links'])->toContain('nullable', 'array');
    expect($rules['social_links.*'])->toContain('nullable', 'url', 'max:255');
});

test('avatar rule rejects URL not belonging to user', function () {
    $request = new UpdateProfileRequest();
    $request->setUserResolver(fn () => $this->user);

    $rules = $request->rules();
    $avatarRule = $rules['avatar'];

    // The avatar rule includes a closure. Test it directly.
    $fail = function ($message) {
        throw new Exception($message);
    };

    // Valid URL belongs to user
    $validUrl = "http://localhost/storage/profiles/avatars/{$this->user->id}/avatar.jpg";
    $closure = $avatarRule[4];
    expect(fn () => $closure('avatar', $validUrl, $fail))->not->toThrow(Exception::class);

    // Invalid URL belongs to other user
    $invalidUrl = "http://localhost/storage/profiles/avatars/other-user/avatar.jpg";
    expect(fn () => $closure('avatar', $invalidUrl, $fail))->toThrow(Exception::class);
});