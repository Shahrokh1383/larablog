<?php

use Modules\About\Http\Requests\StoreTeamMemberRequest;

test('authorize returns true', function () {
    $request = new StoreTeamMemberRequest();
    expect($request->authorize())->toBeTrue();
});

test('rules are correct', function () {
    $request = new StoreTeamMemberRequest();
    expect($request->rules())->toBe([
        'user_id' => ['required', 'exists:users,id', 'unique:about_team_members,user_id'],
        'sort_order' => ['sometimes', 'integer', 'min:0'],
        'is_active' => ['sometimes', 'boolean'],
    ]);
});