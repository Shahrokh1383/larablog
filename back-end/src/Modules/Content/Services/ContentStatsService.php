<?php

namespace Modules\Content\Services;

use Modules\Content\Models\Category;
use Modules\Content\Models\Post;
use Modules\Content\Models\Tag;
use Modules\Content\Services\Contracts\ContentStatsContract;

class ContentStatsService implements ContentStatsContract
{
    public function getDashboardStats(): array
    {
        return [
            'total_posts'        => Post::count(),
            'published_posts'    => Post::published()->count(),
            'total_views'        => (int) Post::sum('views'),
            'popular_categories' => Category::withCount('posts')
                ->orderByDesc('posts_count')
                ->take(5)
                ->get(['id', 'name', 'slug'])
                ->toArray(),
            'popular_tags'       => Tag::withCount('posts')
                ->orderByDesc('posts_count')
                ->take(10)
                ->get(['id', 'name', 'slug'])
                ->toArray(),
        ];
    }
}