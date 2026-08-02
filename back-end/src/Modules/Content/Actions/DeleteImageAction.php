<?php

namespace Modules\Content\Actions;

use Illuminate\Support\Facades\Storage;

class DeleteImageAction
{
    public function execute(string $url): bool
    {
        // Extract the relative path from the URL
        // Assuming Storage::url() returns something like 'http://localhost/storage/posts/images/xyz.jpg'
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