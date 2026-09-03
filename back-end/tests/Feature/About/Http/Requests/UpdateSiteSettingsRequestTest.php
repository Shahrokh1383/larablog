<?php

use Modules\About\Http\Requests\UpdateSiteSettingsRequest;

test('authorize returns true', function () {
    $request = new UpdateSiteSettingsRequest();
    expect($request->authorize())->toBeTrue();
});

test('rules are correct', function () {
    $request = new UpdateSiteSettingsRequest();
    expect($request->rules())->toBe([
        'call_us_phone' => 'nullable|string|max:50',
        'call_us_emails' => 'nullable|array',
        'call_us_emails.*' => 'email',
        'visit_address' => 'nullable|string|max:500',
        'social_links' => 'nullable|array',
        'social_links.linkedin' => 'nullable|url',
        'social_links.github' => 'nullable|url',
        'social_links.twitter' => 'nullable|url',
        'social_links.instagram' => 'nullable|url',
        'social_links.dribbble' => 'nullable|url',
        'social_links.youtube' => 'nullable|url',
        'social_links.discord' => 'nullable|url',
        'story_image' => 'nullable|string|url|max:2048',
    ]);
});