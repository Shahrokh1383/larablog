<?php

namespace Modules\Content\Services;

use Modules\Content\Models\Post;
use Modules\Content\Actions\GenerateSlugAction;
use Modules\Content\Actions\CalculateReadingTimeAction;
use Modules\Content\Actions\AssignTagsToPostAction;
use Modules\Content\DTOs\PostCreateDTO;
use Modules\Content\DTOs\PostUpdateDTO;
use Illuminate\Support\Facades\DB;

class PostService
{
    public function __construct(
        private GenerateSlugAction $generateSlugAction,
        private CalculateReadingTimeAction $calculateReadingTimeAction,
        private AssignTagsToPostAction $assignTagsToPostAction
    ) {}

    public function create(PostCreateDTO $dto): Post
    {
        $slug = $this->generateSlugAction->execute($dto->title, Post::class);
        $readingTime = $this->calculateReadingTimeAction->execute($dto->body);

        $post = DB::transaction(function () use ($dto, $slug, $readingTime) {
            $post = Post::create([
                'title'           => $dto->title,
                'slug'            => $slug,
                'body'            => $dto->body,
                'excerpt'         => $dto->excerpt,
                'featured_image'  => $dto->featuredImage,
                'is_published'    => $dto->isPublished,
                'published_at'    => $dto->publishedAt,
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
            $data['published_at'] = $dto->isPublished ? now() : null; // simple logic
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
}