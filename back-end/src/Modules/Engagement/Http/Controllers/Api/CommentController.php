<?php

namespace Modules\Engagement\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Engagement\Models\Comment;
use Modules\Engagement\Services\CommentService;

class CommentController extends Controller
{
    public function __construct(
        private CommentService $commentService
    ) {}

    public function unreadCount(): JsonResponse
    {
        $count = Comment::where('is_approved', false)->count();
        return response()->json(['unread_count' => $count]);
    }

    public function approve(Comment $comment): JsonResponse
    {
        $this->commentService->approve($comment);
        return response()->json(['message' => 'Comment approved']);
    }

    public function destroy(Comment $comment): JsonResponse
    {
        $this->commentService->delete($comment);
        
        return response()->json(null, 204);
    }
}