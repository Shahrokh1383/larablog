import { useQuery, keepPreviousData } from '@tanstack/react-query';
import { postsApi } from '../api/postsApi';
import { postKeys } from '../index';
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
    queryKey: postKeys.list({ page, search, isEditorsPick }),
    queryFn: () => postsApi.getAll(page, search, isEditorsPick),
    placeholderData: keepPreviousData,
  });
}