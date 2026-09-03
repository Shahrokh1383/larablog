<?php

use Modules\About\Http\Requests\ListTeamMembersRequest;

test('authorize returns true', function () {
    $request = new ListTeamMembersRequest();
    expect($request->authorize())->toBeTrue();
});

test('rules are correct', function () {
    $request = new ListTeamMembersRequest();
    expect($request->rules())->toBe([
        'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
    ]);
});