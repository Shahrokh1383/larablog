import { useQuery, keepPreviousData } from '@tanstack/react-query';
import { tagsApi } from '../api/tagsApi';
import type { PaginatedResponse } from '@/shared/types/api';
import type { Tag } from '../types/tag';

export function useTags(options?: { page?: number; perPage?: number }) {
  const { page = 1, perPage } = options ?? {};

  return useQuery<PaginatedResponse<Tag>>({
    queryKey: ['tags', 'list', page, perPage],
    queryFn: () => tagsApi.getAll({ page, perPage }),
    placeholderData: keepPreviousData,
  });
}

export const tagKeys = {
  all: ['tags'] as const,
  lists: () => [...tagKeys.all, 'list'] as const,
  list: (filters: { page?: number; perPage?: number }) =>
    [...tagKeys.lists(), filters] as const,
};