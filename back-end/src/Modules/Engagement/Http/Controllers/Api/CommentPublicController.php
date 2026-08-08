<?php

namespace Modules\Engagement\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
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

    public function index(IndexCommentRequest $request, string $post): JsonResponse
    {
        $cursor = $request->query('cursor');
    
        $paginator = $this->publicService->getCommentsForPost($post, $cursor);
        $total = $this->publicService->getTotalCommentsCount($post);

        return response()->json([
            'data' => CommentPublicResource::collection($paginator->items()),
            'meta' => [
                'total' => $total,
                'next_cursor' => $paginator->nextCursor()?->encode(),
                'has_more' => $paginator->hasMorePages(),
            ]
        ]);
    }

    public function replies(IndexCommentRequest $request, string $comment): JsonResponse
    {
        $skip = (int) $request->query('skip', 2);
        $take = (int) $request->query('take', 10);

        $result = $this->publicService->getRepliesForComment($comment, $skip, $take);

        return response()->json([
            'data' => CommentPublicResource::collection($result['data']),
            'meta' => $result['meta']
        ]);
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
            isApproved: true,
        );

        $comment = $this->commentService->create($dto);

        return (new CommentPublicResource($comment))
            ->response()
            ->setStatusCode(201);
    }
}