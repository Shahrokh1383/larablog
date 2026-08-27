<?php

namespace Modules\Engagement\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
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
}