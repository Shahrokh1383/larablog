<?php

namespace Modules\Content\Http\Controllers\Api;

use Modules\Content\Models\Post;
use Modules\Content\Services\PostService;
use Modules\Content\Actions\UploadImageAction;
use Modules\Content\Actions\DeleteImageAction;
use Modules\Content\Http\Requests\StorePostRequest;
use Modules\Content\Http\Requests\UpdatePostRequest;
use Modules\Content\Http\Resources\PostResource;
use Modules\Content\DTOs\PostCreateDTO;
use Modules\Content\DTOs\PostUpdateDTO;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class PostController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private PostService $postService
    ) {
        $this->authorizeResource(Post::class, 'post');
    }

    public function index(Request $request)
    {
        $perPage = $request->query('per_page', 15);
        $page = $request->query('page', 1);
        $search = $request->query('search');
        $isEditorsPick = $request->has('is_editors_pick') 
            ? $request->boolean('is_editors_pick') 
            : null;
        $posts = $this->postService->getAll($search, $request->user(), $perPage, $page, $isEditorsPick);
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
            isEditorsPick: $request->validated('is_editors_pick'),
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

    public function uploadImage(Request $request, UploadImageAction $uploadImageAction): JsonResponse
    {
        $request->validate([
            'image' => ['required', 'image', 'max:5120'],
        ]);

        $url = $uploadImageAction->execute($request->file('image'));
        return response()->json(['url' => $url], 200);
    }

    public function deleteImage(Request $request, DeleteImageAction $deleteImageAction): JsonResponse
    {
        $request->validate([
            'url' => ['required', 'string'],
        ]);

        $deleted = $deleteImageAction->execute($request->input('url'));
        
        return response()->json(['success' => $deleted], 200);
    }
}