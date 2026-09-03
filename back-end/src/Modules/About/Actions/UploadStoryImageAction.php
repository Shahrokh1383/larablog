<?php

namespace Modules\About\Actions;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\About\Exceptions\StoryImageUploadFailedException;

class UploadStoryImageAction
{
    private const STORAGE_PATH = 'about/story';

    public function execute(UploadedFile $file): string
    {
        $path = $file->store(self::STORAGE_PATH, 'public');

        if ($path === false) {
            throw new StoryImageUploadFailedException();
        }

        return Storage::disk('public')->url($path);
    }
}