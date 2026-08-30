<?php

use Modules\Engagement\Http\Requests\StoreCommentRequest;
use Modules\Articles\Models\Post;

beforeEach(function () {
    $this->post = Post::factory()->create();
});

test('authorize returns true', function () {
    $request = new StoreCommentRequest();
    expect($request->authorize())->toBeTrue();
});

test('rules require post_id and body with guest name/email conditional', function () {
    $request = new StoreCommentRequest();
    $rules = $request->rules();

    expect($rules)->toHaveKey('post_id');
    expect($rules['post_id'])->toContain('required', 'uuid');

    expect($rules)->toHaveKey('body');
    expect($rules['body'])->toContain('required', 'string', 'max:2000');

    expect($rules)->toHaveKey('name');
    expect($rules['name'])->toContain('nullable', 'string', 'max:255');

    expect($rules)->toHaveKey('email');
    expect($rules['email'])->toContain('nullable', 'email', 'max:255');
});