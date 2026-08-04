<?php

namespace Modules\Content\Services;

use Modules\Content\Models\Tag;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class TagPublicService
{
    public function getPublicTags(?string $search = null, int $perPage = 12): LengthAwarePaginator
    {
        return Tag::search($search)
            ->withCount(['posts as posts_count' => fn($q) => $q->published()])
            ->paginate($perPage);
    }

    public function getPopularTags(int $limit = 10): Collection
    {
        return Tag::withSum(['posts as total_views' => fn($q) => $q->published()], 'views')
            ->withCount(['posts as posts_count' => fn($q) => $q->published()])
            ->orderByDesc('total_views')
            ->take($limit)
            ->get();
    }

    /**
     * Fetches a single tag by slug with counts for the Hero section.
     */
    public function getPublicTagBySlug(string $slug): Tag
    {
        return Tag::where('slug', $slug)
            ->withCount(['posts as posts_count' => fn($q) => $q->published()])
            ->firstOrFail();
    }
}