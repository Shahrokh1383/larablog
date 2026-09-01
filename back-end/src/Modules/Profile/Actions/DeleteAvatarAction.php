<?php

namespace Modules\Profile\Actions;

use Illuminate\Support\Facades\Storage;

class DeleteAvatarAction
{
    public function execute(string $url, string $userId): bool
    {
        $path = parse_url($url, PHP_URL_PATH);
        
        if (!$path || !str_starts_with($path, '/storage/')) {
            return false;
        }

        $relativePath = str_replace('/storage/', '', $path);
        
        if (str_contains($relativePath, '..')) {
            return false;
        }

        $expectedPrefix = "profiles/avatars/{$userId}/";
        if (!str_starts_with($relativePath, $expectedPrefix)) {
            return false;
        }
        
        if (!Storage::disk('public')->exists($relativePath)) {
            return true; 
        }

        return Storage::disk('public')->delete($relativePath);
    }
}