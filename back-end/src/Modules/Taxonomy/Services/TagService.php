<?php

namespace Modules\Taxonomy\Services;

use Modules\Taxonomy\Models\Tag;
use Modules\Articles\Services\Contracts\PostAdminServiceInterface;
use Shared\Actions\GenerateSlugAction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TagService
{
    public function __construct(
        private GenerateSlugAction $generateSlugAction,
        private PostAdminServiceInterface $postAdminService
    ) {}

    public function getAll(int $perPage = 15, int $page = 1): LengthAwarePaginator
    {
        $tags = Tag::orderBy('name')->paginate($perPage, ['*'], 'page', $page);
        $tagIds = $tags->pluck('id')->toArray();

        $counts = $this->postAdminService->getTotalPostCountsByTags($tagIds);

        $tags->each(function ($tag) use ($counts) {
            $tag->posts_count = $counts[$tag->id] ?? 0;
        });

        return $tags;
    }

    public function getWithStats(Tag $tag): Tag
    {
        $counts = $this->postAdminService->getTotalPostCountsByTags([$tag->id]);
        $tag->posts_count = $counts[$tag->id] ?? 0;

        return $tag;
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