<?php

namespace Modules\Content\Services;

use Modules\Content\Models\Post;
use Modules\Content\Models\Category;
use Modules\Content\Actions\MapPostRelationsAction;

class HomePublicService
{
    public function __construct(
        private MapPostRelationsAction $mapPostRelations,
    ) {}

    public function getHomeAggregatedData(): array
    {
        // 1. Fetch Featured Posts (Max 4)
        $featured = Post::with(['category', 'tags'])
            ->published()
            ->where('is_editors_pick', true)
            ->latest('published_at')
            ->take(4)
            ->get();

        $featuredIds = $featured->pluck('id')->toArray();

        // 2. Fetch Recent Posts (Max 6, excluding featured to avoid duplication)
        $recent = Post::with(['category', 'tags'])
            ->published()
            ->when(!empty($featuredIds), fn($q) => $q->whereNotIn('id', $featuredIds))
            ->latest('published_at')
            ->take(6)
            ->get();

        // Map relations efficiently via Action
        $this->mapPostRelations->execute($featured);
        $this->mapPostRelations->execute($recent);

        // 3. Fetch Top 7 Categories
        $categories = Category::withCount(['posts as posts_count' => fn($q) => $q->published()])
            ->orderByDesc('posts_count')
            ->take(7)
            ->get();

        // 4. Fetch Total Posts Count (for the "More" card)
        $totalPostsCount = Post::published()->count();

        return [
            'featured_posts'     => $featured,
            'recent_posts'       => $recent,
            'categories'         => $categories,
            'total_posts_count'  => $totalPostsCount,
        ];
    }
}