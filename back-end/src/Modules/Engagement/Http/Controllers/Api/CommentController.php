<?php

namespace Modules\Engagement\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Modules\Engagement\Http\Requests\IndexCommentAdminRequest;
use Modules\Engagement\Http\Requests\StoreCommentAdminRequest;
use Modules\Engagement\Http\Resources\CommentResource;
use Modules\Engagement\Models\Comment;
use Modules\Engagement\Services\CommentService;

class CommentController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private CommentService $commentService
    ) {}

    public function unreadCount(): JsonResponse
    {
        $count = $this->commentService->getUnreadCount();
        return response()->json(['unread_count' => $count]);
    }

    public function approve(Comment $comment): JsonResponse
    {
        $this->authorize('manage', $comment);
        $this->commentService->approve($comment);
        return response()->json(['message' => 'Comment approved']);
    }

    public function destroy(Comment $comment): JsonResponse
    {
        $this->authorize('delete', $comment);
        $this->commentService->delete($comment);
        return response()->json(null, 204);
    }

    public function index(IndexCommentAdminRequest $request, string $post): JsonResponse
    {
        $perPage = (int) ($request->validated('per_page') ?? 20);

        $comments = $this->commentService->getCommentsForPostAdmin(
            $post,
            $request->user(),
            $perPage,
        );

        return CommentResource::collection($comments)
            ->response()
            ->setStatusCode(200);
    }

    public function store(StoreCommentAdminRequest $request, string $post): JsonResponse
    {
        $this->authorize('create', Comment::class);

        $comment = $this->commentService->createCommentForPost(
            $post,
            $request->user(),
            $request->validated('body'),
            $request->validated('parent_id'),
        );

        return (new CommentResource($comment))
            ->response()
            ->setStatusCode(201);
    }
}