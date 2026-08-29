<?php

namespace Modules\Engagement\Services;

use Modules\Engagement\Models\Comment;
use Modules\Engagement\DTOs\CommentCreateDTO;
use Modules\Engagement\Events\CommentCreated;
use Modules\Engagement\Services\Contracts\CommentServiceInterface;
use Modules\Articles\Services\Contracts\PostAdminServiceInterface;
use Modules\Articles\Services\Contracts\PostInfoContract;
use Shared\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Carbon\Carbon;

class CommentService implements CommentServiceInterface
{
    public function __construct(
        private readonly PostInfoContract $postInfoService
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

    public function getCommentsForPostAdmin(string $postIdentifier, User $user, int $perPage = 20): LengthAwarePaginator
    {
        $postId = $this->resolveViewablePostId($postIdentifier, $user);

        return Comment::with('user')
            ->where('post_id', $postId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

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

    public function getUserCommentsPaginated(string $userId, int $perPage = 15): LengthAwarePaginator
    {
        $paginated = Comment::where('user_id', $userId)
            ->approved()
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        $postIds = $paginated->getCollection()->pluck('post_id')->unique()->toArray();
        $postsMap = $this->postInfoService->getPostsByIds($postIds);

        $paginated->getCollection()->transform(function (Comment $comment) use ($postsMap) {
            $postInfo = $postsMap[$comment->post_id] ?? null;
            $comment->post_slug = $postInfo->slug ?? null;
            $comment->post_title = $postInfo->title ?? null;
            return $comment;
        });

        return $paginated;
    }

    public function getWeeklyTopCommenters(int $limit = 10): Collection
    {
        $startOfWeek = Carbon::now()->startOfWeek();

        return Comment::where('created_at', '>=', $startOfWeek)
            ->approved()
            ->whereNotNull('user_id')
            ->selectRaw('user_id, count(*) as comments_count')
            ->groupBy('user_id')
            ->orderByDesc('comments_count')
            ->limit($limit)
            ->get();
    }

    public function getWeeklyCommentCountForUser(string $userId): int
    {
        $startOfWeek = Carbon::now()->startOfWeek();

        return Comment::where('user_id', $userId)
            ->where('created_at', '>=', $startOfWeek)
            ->approved()
            ->count();
    }

    public function getTotalCommentCountForUser(string $userId): int
    {
        return Comment::where('user_id', $userId)
            ->approved()
            ->count();
    }

    private function resolveViewablePostId(string $postIdentifier, User $user): string
    {
        $postId = $this->postAdminService()->findViewablePostId($postIdentifier, $user);

        if ($postId === null) {
            throw new NotFoundHttpException;
        }

        return $postId;
    }

    private function postAdminService(): PostAdminServiceInterface
    {
        return app(PostAdminServiceInterface::class);
    }
}