import { useMutation, useQueryClient } from '@tanstack/react-query';
import { commentsApi } from '../api/commentsApi';
import { commentKeys } from './useComments';
import { postKeys } from '@/features/posts';
import type { CreateCommentPayload } from '../types/comment';

export function useCreateComment() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (data: CreateCommentPayload) => commentsApi.create(data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: commentKeys.lists() });
      queryClient.invalidateQueries({ queryKey: postKeys.details() });

    },
  });
}