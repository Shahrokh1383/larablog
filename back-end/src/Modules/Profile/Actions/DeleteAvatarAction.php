<?php

namespace Modules\Profile\Actions;

use Illuminate\Support\Facades\Storage;

class DeleteAvatarAction
{
    public function execute(string $url): bool
    {
        $path = parse_url($url, PHP_URL_PATH);
        
        if ($path && str_starts_with($path, '/storage/')) {
            $relativePath = str_replace('/storage/', '', $path);
            
            if (!str_starts_with($relativePath, 'profiles/avatars/')) {
                return false;
            }
            
            if (Storage::disk('public')->exists($relativePath)) {
                return Storage::disk('public')->delete($relativePath);
            }
        }
        
        return false;
    }
}