import { useInfiniteQuery } from '@tanstack/react-query';
import { commentsApi } from '../api/commentsApi';
import type { CommentsResponse } from '../api/commentsApi';

export const commentKeys = {
  all: ['comments'] as const,
  lists: () => [...commentKeys.all, 'list'] as const,
  list: (filter: string) => [...commentKeys.lists(), filter] as const,
  byPost: (postId: string) => [...commentKeys.list(`post-${postId}`)] as const,
};

export function useComments(postId: string) {
  return useInfiniteQuery<CommentsResponse, Error>({
    queryKey: commentKeys.byPost(postId),
    queryFn: ({ pageParam }) => commentsApi.getByPostId(postId, pageParam as string | null),
    initialPageParam: null as string | null,
    getNextPageParam: (lastPage) => {
      return lastPage.meta.has_more ? lastPage.meta.next_cursor : undefined;
    },
    enabled: !!postId,
    staleTime: 1000 * 60 * 2,
    gcTime: 1000 * 60 * 10,
  });
}