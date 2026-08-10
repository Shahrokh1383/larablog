import { useMutation, useQueryClient } from '@tanstack/react-query';
import { commentsApi } from '../api/commentsApi';
import { commentKeys } from './useComments';

export function useReply(postId: string) {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: ({ body, parentId }: { body: string; parentId?: string }) =>
      commentsApi.reply(postId, body, parentId),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: commentKeys.byPost(postId) });
    },
  });
}

export function useApprove(postId: string) {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: (commentId: string) => commentsApi.approve(commentId),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: commentKeys.byPost(postId) });
    },
  });
}

export function useDelete(postId: string) {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: (commentId: string) => commentsApi.delete(commentId),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: commentKeys.byPost(postId) });
    },
  });
}