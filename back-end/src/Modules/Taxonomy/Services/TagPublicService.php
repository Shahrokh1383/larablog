<?php

namespace Modules\Taxonomy\Services;

use Modules\Taxonomy\Models\Tag;
use Modules\Taxonomy\Services\Contracts\TagPublicServiceInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class TagPublicService implements TagPublicServiceInterface
{
    public function getPublicTags(?string $search = null, int $perPage = 12): LengthAwarePaginator
    {
        return Tag::search($search)
            ->withCount(['posts as posts_count' => fn($q) => $q->where('content_posts.is_published', true)])
            ->paginate($perPage);
    }

    public function getPopularTags(int $limit = 10): Collection
    {
        return Tag::withSum(['posts as total_views' => fn($q) => $q->where('content_posts.is_published', true)], 'views')
            ->withCount(['posts as posts_count' => fn($q) => $q->where('content_posts.is_published', true)])
            ->orderByDesc('total_views')
            ->take($limit)
            ->get();
    }

    public function getPublicTagBySlug(string $slug): Tag
    {
        return Tag::where('slug', $slug)
            ->withCount(['posts as posts_count' => fn($q) => $q->where('content_posts.is_published', true)])
            ->firstOrFail();
    }

    public function getTagIdBySlug(string $slug): string
    {
        return Tag::where('slug', $slug)->firstOrFail()->id;
    }

    public function getPopularTagsAsArray(int $limit = 10): array
    {
        return $this->getPopularTags($limit)
            ->map(fn(Tag $tag) => [
                'id'          => $tag->id,
                'name'        => $tag->name,
                'slug'        => $tag->slug,
                'posts_count' => (int) $tag->posts_count,
                'total_views' => (int) $tag->total_views,
            ])
            ->toArray();
    }

    public function getTagStats(): array
    {
        return [
            'total_tags' => Tag::count(),
        ];
    }
}