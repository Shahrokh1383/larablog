<?php

namespace Modules\Articles\Actions;

use Illuminate\Support\Facades\Storage;

class DeleteImageAction
{
    public function execute(string $url): bool
    {
        $path = parse_url($url, PHP_URL_PATH);
        
        if ($path && str_starts_with($path, '/storage/')) {
            $relativePath = str_replace('/storage/', '', $path);
            
            if (Storage::disk('public')->exists($relativePath)) {
                return Storage::disk('public')->delete($relativePath);
            }
        }
        
        return false;
    }
}