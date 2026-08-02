<?php

namespace Modules\Content\Actions;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class UploadImageAction
{
    public function execute(UploadedFile $file): string
    {
        $path = $file->store('posts/images', 'public');
        return Storage::disk('public')->url($path);
    }
}