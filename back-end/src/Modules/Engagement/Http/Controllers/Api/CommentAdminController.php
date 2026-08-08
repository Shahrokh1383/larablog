<?php

namespace Modules\Engagement\Http\Controllers\Api;

use Modules\Engagement\Http\Requests\StoreCommentRequest;
use Modules\Engagement\Http\Resources\CommentResource;
use Modules\Engagement\Services\CommentService;
use Modules\Engagement\DTOs\CommentCreateDTO;
use Modules\Content\Services\Contracts\PostAdminServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Gate;

class CommentAdminController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private CommentService $commentService,
        private PostAdminServiceInterface $postService,
    ) {}

    public function index(Request $request, string $post): JsonResponse
    {
        $postModel = $this->postService->find($post);
        if (!$postModel) {
            abort(404);
        }

        Gate::authorize('view', $postModel);

        $perPage = $request->query('per_page', 20);
        $comments = $this->commentService->getCommentsForPostAdmin($postModel->id, $perPage);

        return CommentResource::collection($comments)
            ->response()
            ->setStatusCode(200);
    }

    public function store(StoreCommentRequest $request, string $post): JsonResponse
    {
        $postModel = $this->postService->find($post);
        if (!$postModel) {
            abort(404);
        }

        Gate::authorize('view', $postModel);

        $user = $request->user();
        $dto = new CommentCreateDTO(
            postId: $postModel->id,
            body: $request->validated('body'),
            parentId: $request->validated('parent_id'),
            userId: $user->id,
            name: $user->name,
            email: $user->email,
            isApproved: true, // Admin/editor/author replies are auto-approved
        );

        $comment = $this->commentService->create($dto);

        return (new CommentResource($comment))
            ->response()
            ->setStatusCode(201);
    }
}