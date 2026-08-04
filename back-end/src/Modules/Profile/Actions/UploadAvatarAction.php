<?php

namespace Modules\Profile\Actions;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class UploadAvatarAction
{
    public function execute(UploadedFile $file): string
    {
        // Store in a dedicated directory to maintain strict Bounded Contexts
        $path = $file->store('profiles/avatars', 'public');
        return Storage::disk('public')->url($path);
    }
}