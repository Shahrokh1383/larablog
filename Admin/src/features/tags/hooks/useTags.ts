import { useQuery, keepPreviousData } from '@tanstack/react-query';
import { tagsApi } from '../api/tagsApi';
import { tagKeys } from '../index';
import type { PaginatedResponse } from '@/shared/types/api';
import type { Tag } from '../types/tag';

export function useTags(options?: { page?: number; perPage?: number }) {
  const { page = 1, perPage } = options ?? {};

  return useQuery<PaginatedResponse<Tag>>({
    queryKey: tagKeys.list({ page, perPage }),
    queryFn: () => tagsApi.getAll({ page, perPage }),
    placeholderData: keepPreviousData,
  });
}