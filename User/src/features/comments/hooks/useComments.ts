import { useInfiniteQuery } from '@tanstack/react-query';
import { commentsApi } from '../api/commentsApi';

export const commentKeys = {
  all: ['comments'] as const,
  lists: () => [...commentKeys.all, 'list'] as const,
  list: (postId: string) => [...commentKeys.lists(), postId] as const,
};

export function useComments(postId: string) {
  return useInfiniteQuery({
    queryKey: commentKeys.list(postId),
    queryFn: ({ pageParam }) => commentsApi.getByPostId(postId, pageParam),
    initialPageParam: null as string | null,
    getNextPageParam: (lastPage) => lastPage.meta.has_more ? lastPage.meta.next_cursor : undefined,
    enabled: !!postId,
  });
}