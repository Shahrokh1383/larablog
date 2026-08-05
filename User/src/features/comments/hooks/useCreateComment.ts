import { useMutation, useQueryClient } from '@tanstack/react-query';
import { commentsApi } from '../api/commentsApi';
import { commentKeys } from './useComments';
import { postKeys } from '@/features/posts/hooks/usePost';
import type { CreateCommentPayload } from '../types/comment';

export function useCreateComment() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (data: CreateCommentPayload) => commentsApi.create(data),
    onSuccess: () => {
      // Refetch comments and update post comments_count
      queryClient.invalidateQueries({ queryKey: commentKeys.lists() });
      queryClient.invalidateQueries({ queryKey: postKeys.details() });
    },
  });
}