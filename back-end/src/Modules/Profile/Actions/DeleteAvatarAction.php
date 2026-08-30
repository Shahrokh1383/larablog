<?php

namespace Modules\Profile\Actions;

use Illuminate\Support\Facades\Storage;

class DeleteAvatarAction
{
    public function execute(string $url): bool
    {
        $path = parse_url($url, PHP_URL_PATH);
        
        if (!$path || !str_starts_with($path, '/storage/')) {
            return false;
        }

        $relativePath = str_replace('/storage/', '', $path);
        
        // Security: Prevent path traversal
        if (str_contains($relativePath, '..')) {
            return false;
        }

        // Security: Restrict to specific directory
        if (!str_starts_with($relativePath, 'profiles/avatars/')) {
            return false;
        }
        
        // Idempotency: If file is already gone, consider it successfully "deleted"
        if (!Storage::disk('public')->exists($relativePath)) {
            return true; 
        }

        return Storage::disk('public')->delete($relativePath);
    }
}