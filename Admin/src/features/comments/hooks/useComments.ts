import { useQuery, keepPreviousData } from '@tanstack/react-query';
import { commentsApi } from '../api/commentsApi';
import type { PaginatedResponse } from '@/shared/types/api';
import type { Comment } from '../types/comment';

export const commentKeys = {
  byPost: (postId: string, page: number = 1) => ['comments', 'post', postId, page] as const,
};

export function useComments(postId: string, page: number = 1) {
  return useQuery<PaginatedResponse<Comment>>({
    queryKey: commentKeys.byPost(postId, page),
    queryFn: () => commentsApi.getByPost(postId, page),
    placeholderData: keepPreviousData,
    enabled: !!postId,
  });
}