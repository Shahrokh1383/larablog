<?php

namespace Modules\Engagement\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Modules\Engagement\Http\Requests\StoreCommentRequest;
use Modules\Engagement\Http\Requests\IndexCommentRequest;
use Modules\Engagement\Http\Resources\CommentPublicResource;
use Modules\Engagement\Services\CommentPublicService;
use Modules\Engagement\Services\CommentService;
use Modules\Engagement\DTOs\CommentCreateDTO;

class CommentPublicController
{
    public function __construct(
        private CommentPublicService $publicService,
        private CommentService $commentService,
    ) {}

    public function index(IndexCommentRequest $request, string $post): AnonymousResourceCollection
    {
        $comments = $this->publicService->getCommentsForPost($post);
        return CommentPublicResource::collection($comments);
    }

    public function store(StoreCommentRequest $request): JsonResponse
    {
        $user = $request->user();

        $dto = new CommentCreateDTO(
            postId: $request->validated('post_id'),
            body: $request->validated('body'),
            parentId: $request->validated('parent_id'),
            userId: $user?->id,
            name: $user ? $user->name : $request->validated('name'),
            email: $user ? $user->email : $request->validated('email'),
            isApproved: $user !== null, // Auto-approve for authenticated users
        );

        $comment = $this->commentService->create($dto);

        return (new CommentPublicResource($comment))
            ->response()
            ->setStatusCode(201);
    }
}