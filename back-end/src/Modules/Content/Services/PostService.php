<?php

namespace Modules\Content\Services;

use Modules\Content\Models\Post;
use Modules\Content\Actions\GenerateSlugAction;
use Modules\Content\Actions\CalculateReadingTimeAction;
use Modules\Content\Actions\AssignTagsToPostAction;
use Modules\Content\DTOs\PostCreateDTO;
use Modules\Content\DTOs\PostUpdateDTO;
use Shared\Contracts\HasRolesContract;
use Modules\Identity\Services\Contracts\AuthorServiceInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;

class PostService
{
    public function __construct(
        private GenerateSlugAction $generateSlugAction,
        private CalculateReadingTimeAction $calculateReadingTimeAction,
        private AssignTagsToPostAction $assignTagsToPostAction,
        private AuthorServiceInterface $authorService,
    ) {}

    public function getAll(?string $search = null, ?HasRolesContract $user = null): Collection
    {
        return Post::with(['user', 'category', 'tags'])
            ->when($user && $user->hasRole('author'), function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->when($search, function ($query) use ($search) {
                $query->where('title', 'like', "%{$search}%")
                      ->orWhere('excerpt', 'like', "%{$search}%");
            })
            ->latest()
            ->get();
    }

    public function create(PostCreateDTO $dto): Post
    {
        $slug = $this->generateSlugAction->execute($dto->title, Post::class);
        $readingTime = $this->calculateReadingTimeAction->execute($dto->body);

        $publishedAt = $dto->publishedAt;
        if ($dto->isPublished && !$publishedAt) {
            $publishedAt = now();
        }

        $post = DB::transaction(function () use ($dto, $slug, $readingTime, $publishedAt) {
            $post = Post::create([
                'title'           => $dto->title,
                'slug'            => $slug,
                'body'            => $dto->body,
                'excerpt'         => $dto->excerpt,
                'featured_image'  => $dto->featuredImage,
                'is_published'    => $dto->isPublished,
                'published_at'    => $publishedAt,
                'reading_time'    => $readingTime,
                'user_id'         => $dto->userId,
                'category_id'     => $dto->categoryId,
            ]);

            if (!empty($dto->tagIds)) {
                $this->assignTagsToPostAction->execute($post, $dto->tagIds);
            }

            return $post;
        });

        return $post;
    }

    public function update(Post $post, PostUpdateDTO $dto): Post
    {
        $data = [];

        if ($dto->title !== null) {
            $data['title'] = $dto->title;
            if ($dto->title !== $post->title) {
                $slug = $this->generateSlugAction->execute($dto->title, Post::class, $post->id);
                $data['slug'] = $slug;
            }
        }

        if ($dto->body !== null) {
            $data['body'] = $dto->body;
            $data['reading_time'] = $this->calculateReadingTimeAction->execute($dto->body);
        }

        foreach (['excerpt', 'featured_image', 'category_id'] as $field) {
            $camelKey = lcfirst(str_replace('_', '', ucwords($field, '_')));
            if ($dto->{$camelKey} !== null) {
                $data[$field] = $dto->{$camelKey};
            }
        }

        if ($dto->isPublished !== null) {
            $data['is_published'] = $dto->isPublished;
            if ($dto->isPublished && $post->published_at === null) {
                $data['published_at'] = $dto->publishedAt ?? now();
            } elseif (!$dto->isPublished) {
                $data['published_at'] = null;
            }
        }

        if ($dto->publishedAt !== null) {
            $data['published_at'] = $dto->publishedAt;
        }

        DB::transaction(function () use ($post, $data, $dto) {
            $post->update($data);

            if ($dto->tagIds !== null) {
                $this->assignTagsToPostAction->execute($post, $dto->tagIds);
            }
        });

        return $post->fresh();
    }

    public function delete(Post $post): void
    {
        $post->delete();
    }

    public function getHomeData(int $perPage = 10): LengthAwarePaginator
    {
        $posts = Post::with(['category', 'tags'])
            ->published()
            ->latest('published_at')
            ->paginate($perPage);

        $authorIds = $posts->pluck('user_id')->unique()->toArray();
        $authors = $this->authorService->getByUserIds($authorIds);

        $posts->getCollection()->transform(function (Post $post) use ($authors) {
            $post->author = isset($authors[$post->user_id])
                ? $authors[$post->user_id]->toArray()
                : null;
            return $post;
        });

        return $posts;
    }

    public function getBySlug(string $slug): ?Post
    {
        $post = Post::with(['category', 'tags'])
            ->where('slug', $slug)
            ->published()
            ->first();

        if (!$post) {
            return null;
        }

        $author = $this->authorService->getByUserId($post->user_id);
        $post->author = $author?->toArray();

        return $post;
    }

    public function getRelatedPosts(string $slug, int $limit = 3): array
    {
        $post = Post::where('slug', $slug)->published()->first();
        if (!$post) {
            return [];
        }

        $related = Post::with(['category', 'tags'])
            ->published()
            ->where('id', '!=', $post->id)
            ->where('category_id', $post->category_id)
            ->latest('published_at')
            ->take($limit)
            ->get();

        $authorIds = $related->pluck('user_id')->unique()->toArray();
        $authors = $this->authorService->getByUserIds($authorIds);

        $related->each(function (Post $p) use ($authors) {
            $p->author = isset($authors[$p->user_id])
                ? $authors[$p->user_id]->toArray()
                : null;
        });

        return $related->all();
    }
}