<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\Profile\Actions\UploadAvatarAction;

beforeEach(function () {
    Storage::fake('public');
    $this->action = new UploadAvatarAction();
});

test('uploads avatar to user-specific directory and returns public URL', function () {
    $userId = 'user-123';
    $file = UploadedFile::fake()->image('avatar.jpg', 100, 100);

    $url = $this->action->execute($file, $userId);

    expect($url)->toContain("/storage/profiles/avatars/{$userId}/");

    $path = str_replace('/storage/', '', parse_url($url, PHP_URL_PATH));
    Storage::disk('public')->assertExists($path);
});

test('stores file with correct extension', function () {
    $userId = 'user-456';
    $file = UploadedFile::fake()->image('photo.png', 200, 200);

    $url = $this->action->execute($file, $userId);
    expect($url)->toContain('.png');
});