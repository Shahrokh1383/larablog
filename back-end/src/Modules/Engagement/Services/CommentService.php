<?php

namespace Modules\Engagement\Services;

use Modules\Engagement\Models\Comment;
use Modules\Engagement\DTOs\CommentCreateDTO;
use Modules\Engagement\Events\CommentCreated;
use Modules\Engagement\Services\Contracts\CommentServiceInterface;
use Illuminate\Support\Facades\DB;

class CommentService implements CommentServiceInterface
{
    public function create(CommentCreateDTO $dto): Comment
    {
        return DB::transaction(function () use ($dto) {
            $parentId = $dto->parentId;

            // Ensure replies are always 1-level deep by attaching replies-to-replies to the root comment.
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
                'is_approved' => $dto->isApproved,
            ]);

            // Dispatch after commit to ensure DB state is persisted before listeners run
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
        $comment->delete();
    }

    public function getCommentCountsForPosts(array $postIds): array
    {
        if (empty($postIds)) return [];

        return Comment::whereIn('post_id', $postIds)
            ->approved()
            ->selectRaw('post_id, count(*) as aggregate')
            ->groupBy('post_id')
            ->pluck('aggregate', 'post_id')
            ->toArray();
    }

    public function getCommentsForPostAdmin(string $postId, int $perPage = 20): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return Comment::with('user')
            ->where('post_id', $postId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }
}