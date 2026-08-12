<?php

namespace Modules\About\Actions;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class UploadStoryImageAction
{
    public function execute(UploadedFile $file): string
    {
        $path = $file->store('about/story', 'public');
        return Storage::disk('public')->url($path);
    }
}