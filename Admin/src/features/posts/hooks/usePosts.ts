import { useQuery, keepPreviousData } from '@tanstack/react-query';
import { postsApi } from '../api/postsApi';
import type { PaginatedResponse } from '@/shared/types/api';
import type { Post } from '../types/post';

interface UsePostsFilters {
  page?: number;
  search?: string;
  isEditorsPick?: boolean;
}

export function usePosts(filters: UsePostsFilters = {}) {
  const { page = 1, search = '', isEditorsPick } = filters;

  return useQuery<PaginatedResponse<Post>>({
    queryKey: ['posts', 'list', page, search, isEditorsPick],
    queryFn: () => postsApi.getAll(page, search, isEditorsPick),
    placeholderData: keepPreviousData,
  });
}

export const postKeys = {
  all: ['posts'] as const,
  lists: () => [...postKeys.all, 'list'] as const,
  list: (filters: { page?: number; search?: string; isEditorsPick?: boolean }) =>
    [...postKeys.lists(), filters] as const,
  details: () => [...postKeys.all, 'detail'] as const,
  detail: (id: string) => [...postKeys.details(), id] as const,
};