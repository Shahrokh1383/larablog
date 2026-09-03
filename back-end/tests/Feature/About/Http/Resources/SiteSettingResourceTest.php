<?php

use Modules\About\Models\SiteSetting;
use Modules\About\Http\Resources\SiteSettingResource;
use Illuminate\Http\Request;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('SiteSettingResource transforms model correctly', function () {
    $settings = SiteSetting::factory()->create([
        'call_us_phone' => '123-456-7890',
        'call_us_emails' => ['contact@example.com'],
        'visit_address' => '123 Main St',
        'social_links' => ['linkedin' => 'https://linkedin.com/in/test'],
        'story_image' => 'http://example.com/storage/about/story/image.jpg',
    ]);

    $resource = new SiteSettingResource($settings);
    $array = $resource->toArray(new Request());

    expect($array)->toHaveKeys(['call_us_phone', 'call_us_emails', 'visit_address', 'social_links', 'story_image']);
    expect($array['call_us_phone'])->toBe('123-456-7890');
    expect($array['call_us_emails'])->toBe(['contact@example.com']);
    expect($array['visit_address'])->toBe('123 Main St');
    expect($array['social_links'])->toBe(['linkedin' => 'https://linkedin.com/in/test']);
    expect($array['story_image'])->toBe('http://example.com/storage/about/story/image.jpg');
});