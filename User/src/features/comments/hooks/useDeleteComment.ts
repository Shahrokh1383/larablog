import { useMutation, useQueryClient } from '@tanstack/react-query';
import type { InfiniteData } from '@tanstack/react-query';
import { commentsApi } from '../api/commentsApi';
import type { CommentsResponse } from '../api/commentsApi';
import { commentKeys } from './useComments';
import { postKeys } from '@/features/posts';

export function useDeleteComment(postId: string) {
  const queryClient = useQueryClient();
  const queryKey = commentKeys.byPost(postId);

  return useMutation({
    mutationFn: (commentId: string) => commentsApi.remove(commentId),

    onSuccess: (_response, commentId) => {
      queryClient.setQueryData<InfiniteData<CommentsResponse>>(queryKey, (oldData) => {
        if (!oldData) return oldData;

        let totalDelta = 0;
        let found = false;

        const pages = oldData.pages.map((page) => {
          const topLevel = page.data.find((comment) => comment.id === commentId);

          if (topLevel) {
            found = true;
            totalDelta += 1 + topLevel.replies_count;
            return { ...page, data: page.data.filter((c) => c.id !== commentId) };
          }

          const hasDeletedReply = page.data.some(
            (comment) => comment.replies?.some((reply) => reply.id === commentId),
          );
          if (!hasDeletedReply) return page;

          found = true;
          totalDelta += 1;
          return {
            ...page,
            data: page.data.map((comment) => ({
              ...comment,
              replies: (comment.replies ?? []).filter((reply) => reply.id !== commentId),
              replies_count: Math.max(0, comment.replies_count - 1),
            })),
          };
        });

        if (!found) return oldData;

        const finalPages = pages.map((page, index) =>
          index === 0
            ? { ...page, meta: { ...page.meta, total: Math.max(0, page.meta.total - totalDelta) } }
            : page,
        );

        return { ...oldData, pages: finalPages };
      });

      queryClient.invalidateQueries({ queryKey: postKeys.details() });
    },
  });
}