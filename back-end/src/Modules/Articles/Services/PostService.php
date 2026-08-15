<?php

namespace Modules\Articles\Services;

use Modules\Articles\Models\Post;
use Modules\Articles\Actions\GenerateSlugAction;
use Modules\Articles\Actions\CalculateReadingTimeAction;
use Modules\Articles\Actions\AssignTagsToPostAction;
use Modules\Articles\Actions\UploadImageAction;
use Modules\Articles\Actions\DeleteImageAction;
use Modules\Articles\DTOs\PostCreateDTO;
use Modules\Articles\DTOs\PostUpdateDTO;
use Shared\Contracts\HasRolesContract;
use Modules\Articles\Services\Contracts\PostAdminServiceInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;

class PostService implements PostAdminServiceInterface
{
    public function __construct(
        private GenerateSlugAction $generateSlugAction,
        private CalculateReadingTimeAction $calculateReadingTimeAction,
        private AssignTagsToPostAction $assignTagsToPostAction,
        private UploadImageAction $uploadImageAction,
        private DeleteImageAction $deleteImageAction,
    ) {}

    public function getAll(?string $search = null, ?HasRolesContract $user = null, int $perPage = 15, int $page = 1, ?bool $isEditorPick = null): LengthAwarePaginator
    {
        return Post::with(['user', 'category', 'tags'])
            ->when($user && $user->hasRole('author'), function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->when($search, function ($query) use ($search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('excerpt', 'like', "%{$search}%");
            })
            ->when($isEditorPick !== null, function ($query) use ($isEditorPick) {
                $query->where('is_editors_pick', $isEditorPick);
            })
            ->latest()
            ->paginate($perPage, ['*'], 'page', $page);
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
        // KISS/DRY Approach: Filter out null values dynamically
        $data = array_filter([
            'title' => $dto->title,
            'body' => $dto->body,
            'excerpt' => $dto->excerpt,
            'featured_image' => $dto->featuredImage,
            'is_published' => $dto->isPublished,
            'is_editors_pick' => $dto->isEditorsPick,
            'category_id' => $dto->categoryId,
        ], fn ($value) => !is_null($value));

        // Regenerate slug if title changed
        if ($dto->title !== null && $dto->title !== $post->title) {
            $data['slug'] = $this->generateSlugAction->execute($dto->title, Post::class, $post->id);
        }

        // Recalculate reading time if body changed
        if ($dto->body !== null) {
            $data['reading_time'] = $this->calculateReadingTimeAction->execute($dto->body);
        }

        // Handle publication dates
        if (isset($data['is_published']) && $data['is_published'] && $post->published_at === null) {
            $data['published_at'] = $dto->publishedAt ?? now();
        } elseif ($dto->publishedAt !== null) {
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

    public function find(string $id): ?Post
    {
        return Post::find($id);
    }

    // Fixing Article I Violation: Moving logic from Controller to Service
    public function uploadImage(UploadedFile $file): string
    {
        return $this->uploadImageAction->execute($file);
    }

    public function deleteImage(string $url): bool
    {
        return $this->deleteImageAction->execute($url);
    }
}