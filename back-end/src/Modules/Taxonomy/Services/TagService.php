<?php

namespace Modules\Taxonomy\Services;

use Modules\Taxonomy\Models\Tag;
use Shared\Actions\GenerateSlugAction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class TagService
{
    public function __construct(
        private GenerateSlugAction $generateSlugAction
    ) {}

    public function getAll(int $perPage = 15, int $page = 1): LengthAwarePaginator
    {
        $postsCountSubQuery = DB::table('content_post_tag')
            ->selectRaw('count(*)')
            ->whereColumn('content_post_tag.tag_id', 'content_tags.id');

        return Tag::select('content_tags.*')
            ->addSelect([
                'posts_count' => $postsCountSubQuery,
            ])
            ->orderBy('name')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function create(string $name): Tag
    {
        $slug = $this->generateSlugAction->execute($name, Tag::class);

        return Tag::create([
            'name' => $name,
            'slug' => $slug,
        ]);
    }

    public function update(Tag $tag, string $name): Tag
    {
        if ($name !== $tag->name) {
            $slug = $this->generateSlugAction->execute($name, Tag::class, $tag->id);
            $tag->update(['name' => $name, 'slug' => $slug]);
        }

        return $tag;
    }

    public function delete(Tag $tag): void
    {
        $tag->delete();
    }
}