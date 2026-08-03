import { useQuery, keepPreviousData } from '@tanstack/react-query';
import { categoriesApi } from '../api/categoriesApi';
import type { PaginatedResponse } from '@/shared/types/api';
import type { Category } from '../types/category';

export function useCategories(options?: { page?: number; perPage?: number }) {
  const { page = 1, perPage } = options ?? {};

  return useQuery<PaginatedResponse<Category>>({
    queryKey: ['categories', 'list', page, perPage],
    queryFn: () => categoriesApi.getAll({ page, perPage }),
    placeholderData: keepPreviousData,
  });
}

export const categoryKeys = {
  all: ['categories'] as const,
  lists: () => [...categoryKeys.all, 'list'] as const,
  list: (filters: { page?: number; perPage?: number }) =>
    [...categoryKeys.lists(), filters] as const,
};