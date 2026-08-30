<?php

namespace Modules\Profile\Actions;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class UploadAvatarAction
{
    public function execute(UploadedFile $file, string $userId): string
    {
        // Store in a user-specific directory to enforce strict ownership
        $path = $file->store("profiles/avatars/{$userId}", 'public');
        return Storage::disk('public')->url($path);
    }
}