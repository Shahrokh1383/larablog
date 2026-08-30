<?php

use Modules\Engagement\Http\Requests\IndexCommentAdminRequest;

test('authorize returns true', function () {
    $request = new IndexCommentAdminRequest();
    expect($request->authorize())->toBeTrue();
});

test('rules are correct', function () {
    $request = new IndexCommentAdminRequest();
    $rules = $request->rules();

    expect($rules)->toHaveKey('per_page');
    expect($rules['per_page'])->toBe(['nullable', 'integer', 'min:1', 'max:100']);
});