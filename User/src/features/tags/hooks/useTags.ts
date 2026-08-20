import { useQuery } from '@tanstack/react-query';
import { tagsApi } from '../api/tagsApi';
import type { PaginatedResponse } from '@/shared/types/api';
import type { Tag } from '../types/tag';

export const tagKeys = {
  all: ['tags'] as const,
  lists: () => [...tagKeys.all, 'list'] as const,
  list: (filters: { search?: string; page?: number; per_page?: number }) => [...tagKeys.lists(), filters] as const,
  popular: () => [...tagKeys.all, 'popular'] as const,
};

interface UseTagsOptions {
  search?: string;
  page?: number;
  per_page?: number;
}

export function useTags(options: UseTagsOptions = {}) {
  const { search, page = 1, per_page = 12 } = options;

  return useQuery<PaginatedResponse<Tag>>({
    queryKey: tagKeys.list({ search, page, per_page }),
    queryFn: () => tagsApi.getAll({ search, page, per_page }),
    enabled: per_page > 0,
    placeholderData: (previousData) => previousData,
  });
}