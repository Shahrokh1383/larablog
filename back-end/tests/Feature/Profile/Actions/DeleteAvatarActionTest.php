<?php

use Illuminate\Support\Facades\Storage;
use Modules\Profile\Actions\DeleteAvatarAction;

beforeEach(function () {
    Storage::fake('public');
    $this->action = new DeleteAvatarAction();
});

test('deletes file if path is valid and belongs to user', function () {
    $userId = 'user-123';
    $filePath = "profiles/avatars/{$userId}/avatar.jpg";
    Storage::disk('public')->put($filePath, 'dummy');

    $url = Storage::disk('public')->url($filePath);
    $result = $this->action->execute($url, $userId);

    expect($result)->toBeTrue();
    Storage::disk('public')->assertMissing($filePath);
});

test('returns true if file already missing', function () {
    $userId = 'user-123';
    $filePath = "profiles/avatars/{$userId}/avatar.jpg";
    $url = Storage::disk('public')->url($filePath);

    $result = $this->action->execute($url, $userId);
    expect($result)->toBeTrue();
});

test('returns false if path is not under storage', function () {
    $result = $this->action->execute('http://example.com/avatar.jpg', 'user-123');
    expect($result)->toBeFalse();
});

test('returns false if path contains traversal', function () {
    $url = 'http://localhost/storage/profiles/avatars/user-123/../../secret.jpg';
    $result = $this->action->execute($url, 'user-123');
    expect($result)->toBeFalse();
});

test('returns false if path does not start with expected user prefix', function () {
    $url = 'http://localhost/storage/profiles/avatars/other-user/avatar.jpg';
    $result = $this->action->execute($url, 'user-123');
    expect($result)->toBeFalse();
});