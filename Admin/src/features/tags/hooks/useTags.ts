import { useQuery, keepPreviousData } from '@tanstack/react-query';
import { tagsApi } from '../api/tagsApi';
import type { PaginatedResponse } from '@/shared/types/api';
import type { Tag } from '../types/tag';

export function useTags(page = 1) {
  return useQuery<PaginatedResponse<Tag>>({
    queryKey: ['tags', 'list', page],
    queryFn: () => tagsApi.getAll(page),
    placeholderData: keepPreviousData,
  });
}

export const tagKeys = {
  all: ['tags'] as const,
  lists: () => [...tagKeys.all, 'list'] as const,
  list: (filters: { page?: number }) =>
    [...tagKeys.lists(), filters] as const,
};