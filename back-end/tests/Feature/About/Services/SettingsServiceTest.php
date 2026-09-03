<?php

use Modules\About\Services\SettingsService;
use Modules\About\Models\SiteSetting;
use Modules\About\DTOs\SiteSettingsDTO;
use Modules\About\Actions\UploadStoryImageAction;
use Modules\About\Actions\DeleteStoryImageAction;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
    $this->uploadAction = Mockery::mock(UploadStoryImageAction::class);
    $this->deleteAction = Mockery::mock(DeleteStoryImageAction::class);
    $this->service = new SettingsService($this->uploadAction, $this->deleteAction);

    $this->settings = SiteSetting::firstOrFail();
});

test('getSettings returns singleton', function () {
    $result = $this->service->getSettings();
    expect($result->id)->toBe($this->settings->id);
});

test('updateSettings updates and returns fresh', function () {
    $dto = new SiteSettingsDTO(
        callUsPhone: 'new phone',
        callUsEmails: ['new@example.com'],
        visitAddress: 'new address',
        socialLinks: ['linkedin' => 'https://linkedin.com/in/new'],
        storyImage: 'http://example.com/storage/about/story/new.jpg'
    );

    $updated = $this->service->updateSettings($dto);

    expect($updated->call_us_phone)->toBe('new phone');
    expect($updated->call_us_emails)->toBe(['new@example.com']);
    expect($updated->visit_address)->toBe('new address');
    expect($updated->social_links)->toBe(['linkedin' => 'https://linkedin.com/in/new']);
    expect($updated->story_image)->toBe('http://example.com/storage/about/story/new.jpg');
    $this->assertDatabaseHas('about_site_settings', ['call_us_phone' => 'new phone']);
});

test('uploadStoryImage delegates to action', function () {
    $file = UploadedFile::fake()->image('story.jpg');
    $this->uploadAction->shouldReceive('execute')->once()->with($file)->andReturn('http://example.com/storage/about/story/image.jpg');

    $url = $this->service->uploadStoryImage($file);
    expect($url)->toBe('http://example.com/storage/about/story/image.jpg');
});

test('deleteStoryImage clears story_image if matches', function () {
    // Set a story_image on the singleton row
    $this->settings->update(['story_image' => 'http://example.com/storage/about/story/image.jpg']);

    $this->deleteAction->shouldReceive('execute')->once()
        ->with($this->settings->story_image)
        ->andReturn('/storage/about/story/image.jpg');

    $this->service->deleteStoryImage($this->settings->story_image);

    $this->settings->refresh();
    expect($this->settings->story_image)->toBeNull();
});

test('deleteStoryImage does not clear if different path', function () {
    // Set a story_image on the singleton row
    $this->settings->update(['story_image' => 'http://example.com/storage/about/story/image.jpg']);

    $this->deleteAction->shouldReceive('execute')->once()
        ->with('http://example.com/storage/about/story/other.jpg')
        ->andReturn('/storage/about/story/other.jpg');

    $this->service->deleteStoryImage('http://example.com/storage/about/story/other.jpg');

    $this->settings->refresh();
    expect($this->settings->story_image)->toBe('http://example.com/storage/about/story/image.jpg');
});