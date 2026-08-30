<?php

use Modules\Engagement\Http\Requests\StoreCommentAdminRequest;
use Modules\Articles\Models\Post;

beforeEach(function () {
    $this->post = Post::factory()->create();
});

test('authorize returns true', function () {
    $request = new StoreCommentAdminRequest();
    expect($request->authorize())->toBeTrue();
});

test('rules require body and optional parent_id with post constraint', function () {
    $request = new StoreCommentAdminRequest();
    $rules = $request->rules();

    expect($rules)->toHaveKey('body');
    expect($rules['body'])->toContain('required', 'string', 'max:2000');

    expect($rules)->toHaveKey('parent_id');
    expect($rules['parent_id'])->toContain('nullable', 'uuid');
});