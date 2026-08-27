<?php

namespace Modules\Engagement\Http\Controllers\Api;

use Modules\Engagement\Http\Requests\IndexCommentAdminRequest;
use Modules\Engagement\Http\Requests\StoreCommentAdminRequest;
use Modules\Engagement\Http\Resources\CommentResource;
use Modules\Engagement\Models\Comment;
use Modules\Engagement\Services\CommentService;
use Modules\Engagement\DTOs\CommentCreateDTO;
use Modules\Articles\Services\Contracts\PostAdminServiceInterface;
use Illuminate\Http\JsonResponse;
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

    public function index(IndexCommentAdminRequest $request, string $post): JsonResponse
    {
        $postModel = $this->postService->find($post);
        if (!$postModel) {
            abort(404);
        }

        Gate::authorize('view', $postModel);

        // Null-coalescing (not validated()'s default arg): an explicitly empty
        // "?per_page=" is normalized to null and must still fall back to 20.
        $perPage = (int) ($request->validated('per_page') ?? 20);
        $comments = $this->commentService->getCommentsForPostAdmin($postModel->id, $perPage);

        return CommentResource::collection($comments)
            ->response()
            ->setStatusCode(200);
    }

    public function store(StoreCommentAdminRequest $request, string $post): JsonResponse
    {
        $postModel = $this->postService->find($post);
        if (!$postModel) {
            abort(404);
        }

        Gate::authorize('view', $postModel);
        // Self-documenting authorization intent; a no-op today, enforced the
        // moment CommentPolicy::create tightens.
        $this->authorize('create', Comment::class);

        $user = $request->user();

        // Staff replies are auto-approved because they carry an attributable
        // identity — the service derives is_approved from userId.
        $dto = new CommentCreateDTO(
            postId: $postModel->id,
            body: $request->validated('body'),
            parentId: $request->validated('parent_id'),
            userId: $user->id,
            name: $user->name,
            email: $user->email,
        );

        $comment = $this->commentService->create($dto);

        return (new CommentResource($comment))
            ->response()
            ->setStatusCode(201);
    }
}