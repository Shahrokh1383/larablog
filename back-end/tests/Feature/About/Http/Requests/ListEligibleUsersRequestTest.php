<?php

use Modules\About\Http\Requests\ListEligibleUsersRequest;

test('authorize returns true', function () {
    $request = new ListEligibleUsersRequest();
    expect($request->authorize())->toBeTrue();
});

test('rules are correct', function () {
    $request = new ListEligibleUsersRequest();
    expect($request->rules())->toBe([
        'search' => ['sometimes', 'nullable', 'string', 'max:255'],
        'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
    ]);
});