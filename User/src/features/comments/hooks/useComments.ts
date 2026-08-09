import { useQuery, keepPreviousData } from '@tanstack/react-query';
import { commentsApi } from '../api/commentsApi';

// Article VI Compliance: Structured Query Keys
export const commentKeys = {
  all: ['comments'] as const,
  lists: () => [...commentKeys.all, 'list'] as const,
  list: (filter: string) => [...commentKeys.lists(), filter] as const,
  byPost: (postId: string, cursor?: string) => [...commentKeys.list(`post-${postId}`), { cursor }] as const,
};

export function useComments(postId: string, cursor?: string) {
  return useQuery({
    queryKey: commentKeys.byPost(postId, cursor),
    queryFn: () => commentsApi.getByPostId(postId, cursor),
    placeholderData: keepPreviousData,
    enabled: !!postId,
    staleTime: 1000 * 60 * 2, // 2 minutes (Prevents aggressive refetching)
    gcTime: 1000 * 60 * 10,   // 10 minutes (Persistent memory caching)
  });
}