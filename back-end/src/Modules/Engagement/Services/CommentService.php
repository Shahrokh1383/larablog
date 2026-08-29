<?php

namespace Modules\Engagement\Services;

use Modules\Engagement\Models\Comment;
use Modules\Engagement\DTOs\CommentCreateDTO;
use Modules\Engagement\Events\CommentCreated;
use Modules\Articles\Services\Contracts\PostAdminServiceInterface;
use Shared\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CommentService
{
    public function __construct(
        private readonly PostAdminServiceInterface $postAdminService,
    ) {}

    public function create(CommentCreateDTO $dto): Comment
    {
        return DB::transaction(function () use ($dto) {
            $parentId = $dto->parentId;

            if ($parentId) {
                $parentComment = Comment::find($parentId);
                if ($parentComment && $parentComment->parent_id) {
                    $parentId = $parentComment->parent_id;
                }
            }

            $comment = Comment::create([
                'post_id'     => $dto->postId,
                'parent_id'   => $parentId,
                'user_id'     => $dto->userId,
                'name'        => $dto->name,
                'email'       => $dto->email,
                'body'        => $dto->body,
                'is_approved' => $dto->userId !== null,
            ]);

            DB::afterCommit(function () use ($comment) {
                event(new CommentCreated($comment));
            });

            return $comment;
        });
    }

    public function approve(Comment $comment): void
    {
        $comment->update(['is_approved' => true]);
    }

    public function delete(Comment $comment): void
    {
        DB::transaction(function () use ($comment): void {
            if ($comment->parent_id === null) {
                Comment::where('parent_id', $comment->id)->delete();
            }

            $comment->delete();
        });
    }

    public function getUnreadCount(): int
    {
        return Comment::where('is_approved', false)->count();
    }

    public function getCommentsForPostAdmin(string $postIdentifier, User $user, int $perPage = 20): LengthAwarePaginator
    {
        $postId = $this->resolveViewablePostId($postIdentifier, $user);

        return Comment::with('user')
            ->where('post_id', $postId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Staff reply to a post. Staff replies carry an attributable identity;
     * is_approved is derived from a non-null userId, so they publish instantly.
     */
    public function createCommentForPost(
        string $postIdentifier,
        User $user,
        string $body,
        ?string $parentId = null,
    ): Comment {
        $postId = $this->resolveViewablePostId($postIdentifier, $user);

        return $this->create(new CommentCreateDTO(
            postId: $postId,
            body: $body,
            parentId: $parentId,
            userId: $user->id,
            name: $user->name,
            email: $user->email,
        ));
    }

    private function resolveViewablePostId(string $postIdentifier, User $user): string
    {
        $postId = $this->postAdminService->findViewablePostId($postIdentifier, $user);

        if ($postId === null) {
            throw new NotFoundHttpException;
        }

        return $postId;
    }
}