import { useQuery, keepPreviousData } from '@tanstack/react-query';
import { postsApi } from '../api/postsApi';
import type { PaginatedResponse } from '@/shared/types/api';
import type { Post } from '../types/post';

export function usePosts(page = 1, search = '') {
  return useQuery<PaginatedResponse<Post>>({
    queryKey: ['posts', 'list', page, search],
    queryFn: () => postsApi.getAll(page, search),
    placeholderData: keepPreviousData, // smooth pagination transition
  });
}

export const postKeys = {
  all: ['posts'] as const,
  lists: () => [...postKeys.all, 'list'] as const,
  list: (filters: { page?: number; search?: string }) =>
    [...postKeys.lists(), filters] as const,
  details: () => [...postKeys.all, 'detail'] as const,
  detail: (id: string) => [...postKeys.details(), id] as const,
};