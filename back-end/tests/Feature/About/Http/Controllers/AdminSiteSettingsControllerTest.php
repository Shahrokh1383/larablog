<?php

use Modules\About\Models\SiteSetting;
use Modules\About\Actions\UploadStoryImageAction;
use Modules\About\Actions\DeleteStoryImageAction;
use Shared\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');
    $this->uploadAction = Mockery::mock(UploadStoryImageAction::class);
    $this->deleteAction = Mockery::mock(DeleteStoryImageAction::class);
    $this->app->instance(UploadStoryImageAction::class, $this->uploadAction);
    $this->app->instance(DeleteStoryImageAction::class, $this->deleteAction);

    // Create settings row
    SiteSetting::factory()->create();

    // Ensure admin role exists
    Role::firstOrCreate(['name' => 'admin']);
    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');
    $this->actingAs($this->admin, 'sanctum');
});

test('admin can view site settings', function () {
    $response = $this->getJson('/api/admin/about/settings');
    $response->assertStatus(200)
        ->assertJsonStructure(['data' => ['call_us_phone', 'call_us_emails', 'visit_address', 'social_links', 'story_image']]);
});

test('admin can update site settings', function () {
    $response = $this->putJson('/api/admin/about/settings', [
        'call_us_phone' => '123',
        'call_us_emails' => ['test@example.com'],
        'visit_address' => 'Address',
        'social_links' => ['linkedin' => 'https://linkedin.com/in/test'],
        'story_image' => null,
    ]);

    $response->assertStatus(200)
        ->assertJson(['message' => 'Site settings updated successfully.'])
        ->assertJsonPath('data.call_us_phone', '123');
});

test('admin can upload story image', function () {
    $file = UploadedFile::fake()->image('story.jpg');
    $this->uploadAction->shouldReceive('execute')->once()->with($file)->andReturn('http://example.com/storage/about/story/image.jpg');

    $response = $this->postJson('/api/admin/about/upload-story-image', [
        'story_image' => $file,
    ]);

    $response->assertStatus(200)
        ->assertJson(['url' => 'http://example.com/storage/about/story/image.jpg']);
});

test('admin can delete story image', function () {
    $this->deleteAction->shouldReceive('execute')->once()->andReturn('/storage/about/story/image.jpg');

    $response = $this->deleteJson('/api/admin/about/delete-story-image', [
        'url' => 'http://example.com/storage/about/story/image.jpg',
    ]);

    $response->assertStatus(200)
        ->assertJson(['message' => 'Story image deleted successfully.']);
});

test('non-admin cannot view settings', function () {
    $user = User::factory()->create();
    $this->actingAs($user, 'sanctum');

    $response = $this->getJson('/api/admin/about/settings');
    $response->assertStatus(403);
});