<?php

namespace Modules\Content\Http\Controllers\Api;

use Modules\Content\Models\Post;
use Modules\Content\Services\PostService;
use Modules\Content\Http\Requests\StorePostRequest;
use Modules\Content\Http\Requests\UpdatePostRequest;
use Modules\Content\Http\Resources\PostResource;
use Modules\Content\DTOs\PostCreateDTO;
use Modules\Content\DTOs\PostUpdateDTO;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class PostController extends Controller
{
    public function __construct(
        private PostService $postService
    ) {
        $this->authorizeResource(Post::class, 'post');
    }

    public function index()
    {
        $posts = Post::with(['user', 'category', 'tags'])->latest()->get();
        return PostResource::collection($posts);
    }

    public function store(StorePostRequest $request): PostResource
    {
        $dto = new PostCreateDTO(
            title: $request->validated('title'),
            body: $request->validated('body'),
            userId: $request->user()->id,
            excerpt: $request->validated('excerpt'),
            featuredImage: $request->validated('featured_image'),
            isPublished: $request->boolean('is_published'),
            categoryId: $request->validated('category_id'),
            tagIds: $request->validated('tag_ids', []),
        );

        $post = $this->postService->create($dto);
        return new PostResource($post->load(['user', 'category', 'tags']));
    }

    public function show(Post $post): PostResource
    {
        return new PostResource($post->load(['user', 'category', 'tags']));
    }

    public function update(UpdatePostRequest $request, Post $post): PostResource
    {
        $dto = new PostUpdateDTO(
            title: $request->validated('title'),
            body: $request->validated('body'),
            excerpt: $request->validated('excerpt'),
            featuredImage: $request->validated('featured_image'),
            isPublished: $request->validated('is_published'),
            categoryId: $request->validated('category_id'),
            tagIds: $request->validated('tag_ids'),
        );

        $post = $this->postService->update($post, $dto);
        return new PostResource($post->load(['user', 'category', 'tags']));
    }

    public function destroy(Post $post): JsonResponse
    {
        $this->postService->delete($post);
        return response()->json(null, 204);
    }
}