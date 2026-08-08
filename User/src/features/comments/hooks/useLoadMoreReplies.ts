import { useMutation, useQueryClient } from '@tanstack/react-query';
import { commentsApi } from '../api/commentsApi';
import { commentKeys } from './useComments';
import type { InfiniteData } from '@tanstack/react-query';
import type { CommentsResponse } from '../api/commentsApi';

export function useLoadMoreReplies(postId: string) {
  const queryClient = useQueryClient();
  const queryKey = commentKeys.list(postId);

  return useMutation({
    mutationFn: (commentId: string) => {
      // Read current replies length from cache to calculate skip
      const queryData = queryClient.getQueryData<InfiniteData<CommentsResponse>>(queryKey);
      let skip = 0;
      
      if (queryData) {
        for (const page of queryData.pages) {
          const comment = page.data.find(c => c.id === commentId);
          if (comment) {
            skip = comment.replies?.length || 0;
            break;
          }
        }
      }
      
      return commentsApi.getReplies(commentId, skip);
    },
    onSuccess: (data, commentId) => {
      // Update the cache centrally
      queryClient.setQueryData<InfiniteData<CommentsResponse>>(queryKey, (oldData) => {
        if (!oldData) return oldData;
        
        const newPages = oldData.pages.map(page => {
          const newData = page.data.map(comment => {
            if (comment.id === commentId) {
              return {
                ...comment,
                replies: [...(comment.replies || []), ...data.data],
                replies_has_more: data.meta.has_more,
              };
            }
            return comment;
          });
          return { ...page, data: newData };
        });

        return { ...oldData, pages: newPages };
      });
    }
  });
}