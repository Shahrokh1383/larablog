<?php

namespace Modules\Engagement\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Modules\Engagement\Http\Requests\IndexCommentRequest;
use Modules\Engagement\Http\Requests\StoreCommentRequest;
use Modules\Engagement\Http\Resources\CommentPublicResource;
use Modules\Engagement\Models\Comment;
use Modules\Engagement\Services\CommentPublicService;
use Modules\Engagement\Services\CommentService;
use Modules\Engagement\DTOs\CommentCreateDTO;

class CommentPublicController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private CommentPublicService $publicService,
        private CommentService $commentService,
    ) {}

    public function index(IndexCommentRequest $request, string $post): JsonResponse
    {
        $cursor = $request->validated('cursor');

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
        // Defaults reference the service constants: the first PRELOADED_REPLIES_LIMIT
        // replies are already rendered inline, so pagination continues from there.
        $skip = (int) ($request->validated('skip') ?? CommentPublicService::PRELOADED_REPLIES_LIMIT);
        $take = (int) ($request->validated('take') ?? CommentPublicService::REPLIES_PER_PAGE);

        $result = $this->publicService->getRepliesForComment($comment, $skip, $take);

        return response()->json([
            'data' => CommentPublicResource::collection($result['data']),
            'meta' => $result['meta']
        ]);
    }

    public function store(StoreCommentRequest $request): JsonResponse
    {
        $this->authorize('create', Comment::class);

        $user = $request->user();

        // isApproved is deliberately NOT passed: the service derives it from
        // the presence of an attributable identity (guests await moderation).
        $dto = new CommentCreateDTO(
            postId: $request->validated('post_id'),
            body: $request->validated('body'),
            parentId: $request->validated('parent_id'),
            userId: $user?->id,
            name: $user ? $user->name : $request->validated('name'),
            email: $user ? $user->email : $request->validated('email'),
        );

        $comment = $this->commentService->create($dto);

        return (new CommentPublicResource($comment))
            ->response()
            ->setStatusCode(201);
    }

    public function destroy(Comment $comment): JsonResponse
    {
        $this->authorize('delete', $comment);
        $this->commentService->delete($comment);
        return response()->json(null, 204);
    }
}