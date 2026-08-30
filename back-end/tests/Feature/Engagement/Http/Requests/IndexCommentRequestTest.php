<?php

use Modules\Engagement\Http\Requests\IndexCommentRequest;

test('authorize returns true', function () {
    $request = new IndexCommentRequest();
    expect($request->authorize())->toBeTrue();
});

test('rules contain cursor, skip, take with correct constraints', function () {
    $request = new IndexCommentRequest();
    $rules = $request->rules();

    expect($rules)->toHaveKeys(['cursor', 'skip', 'take']);
    expect($rules['cursor'])->toContain('nullable', 'string', 'max:255');
    expect($rules['skip'])->toBe(['nullable', 'integer', 'min:0', 'max:1000']);
    expect($rules['take'])->toBe(['nullable', 'integer', 'min:1', 'max:50']);
});