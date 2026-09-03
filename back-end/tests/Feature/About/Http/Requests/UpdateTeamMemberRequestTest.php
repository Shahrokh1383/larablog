<?php

use Modules\About\Http\Requests\UpdateTeamMemberRequest;
use Modules\About\Models\TeamMember;
use Illuminate\Validation\Rule;
use Illuminate\Routing\Route;

test('authorize returns true', function () {
    $request = new UpdateTeamMemberRequest();
    expect($request->authorize())->toBeTrue();
});

test('rules include unique ignoring current member', function () {
    $member = TeamMember::factory()->create();

    $route = Mockery::mock(Route::class);
    $route->shouldReceive('parameter')
        ->with('team_member', null)
        ->andReturn($member->id);

    $request = new UpdateTeamMemberRequest();
    $request->setContainer(app());
    $request->setRouteResolver(fn () => $route);

    $rules = $request->rules();

    $expectedUnique = Rule::unique('about_team_members', 'user_id')->ignore($member->id);

    // toContainEqual uses == for comparison, which works for objects with protected properties
    expect($rules['user_id'])->toContainEqual($expectedUnique);

    // Additional sanity check: there is a Unique rule present
    $uniqueRule = collect($rules['user_id'])->first(fn ($rule) => $rule instanceof \Illuminate\Validation\Rules\Unique);
    expect($uniqueRule)->not->toBeNull();
});