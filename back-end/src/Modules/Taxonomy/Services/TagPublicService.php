<?php

namespace Modules\Taxonomy\Services;

use Modules\Taxonomy\Models\Tag;
use Modules\Taxonomy\Services\Contracts\TagPublicServiceInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class TagPublicService implements TagPublicServiceInterface
{
    public function getPublicTags(?string $search = null, int $perPage = 12): LengthAwarePaginator
    {
        $postsCountSubQuery = DB::table('content_post_tag')
            ->selectRaw('count(*)')
            ->whereColumn('content_post_tag.tag_id', 'content_tags.id')
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('content_posts')
                    ->whereColumn('content_posts.id', 'content_post_tag.post_id')
                    ->where('content_posts.is_published', true);
            });

        return Tag::select('content_tags.*')
            ->addSelect([
                'posts_count' => $postsCountSubQuery,
            ])
            ->search($search)
            ->paginate($perPage);
    }

    public function getPopularTags(int $limit = 10): array
    {
        $postsCountSubQuery = DB::table('content_post_tag')
            ->selectRaw('count(*)')
            ->whereColumn('content_post_tag.tag_id', 'content_tags.id')
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('content_posts')
                    ->whereColumn('content_posts.id', 'content_post_tag.post_id')
                    ->where('content_posts.is_published', true);
            });

        $totalViewsSubQuery = DB::table('content_post_tag')
            ->join('content_posts', 'content_posts.id', '=', 'content_post_tag.post_id')
            ->whereColumn('content_post_tag.tag_id', 'content_tags.id')
            ->where('content_posts.is_published', true)
            ->selectRaw('COALESCE(sum(content_posts.views), 0)');

        return Tag::select('content_tags.*')
            ->addSelect([
                'posts_count' => $postsCountSubQuery,
                'total_views' => $totalViewsSubQuery,
            ])
            ->orderByDesc('total_views')
            ->take($limit)
            ->get()
            ->map(fn(Tag $tag) => [
                'id'          => $tag->id,
                'name'        => $tag->name,
                'slug'        => $tag->slug,
                'posts_count' => (int) $tag->posts_count,
                'total_views' => (int) $tag->total_views,
            ])
            ->toArray();
    }

    public function getPublicTagBySlug(string $slug): Tag
    {
        $postsCountSubQuery = DB::table('content_post_tag')
            ->selectRaw('count(*)')
            ->whereColumn('content_post_tag.tag_id', 'content_tags.id')
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('content_posts')
                    ->whereColumn('content_posts.id', 'content_post_tag.post_id')
                    ->where('content_posts.is_published', true);
            });

        return Tag::select('content_tags.*')
            ->addSelect([
                'posts_count' => $postsCountSubQuery,
            ])
            ->where('slug', $slug)
            ->firstOrFail();
    }

    public function getTagIdBySlug(string $slug): string
    {
        return Tag::where('slug', $slug)->firstOrFail()->id;
    }

    public function getPopularTagsAsArray(int $limit = 10): array
    {
        // Simplified to directly return the array from getPopularTags,
        // eliminating redundant mapping logic (Dead Code Removal).
        return $this->getPopularTags($limit);
    }

    public function getTagStats(): array
    {
        return [
            'total_tags' => Tag::count(),
        ];
    }
}