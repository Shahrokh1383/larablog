import { useQuery, keepPreviousData } from '@tanstack/react-query';
import { categoriesApi } from '../api/categoriesApi';
import type { PaginatedResponse } from '@/shared/types/api';
import type { Category } from '../types/category';

export function useCategories(page = 1) {
  return useQuery<PaginatedResponse<Category>>({
    queryKey: ['categories', 'list', page],
    queryFn: () => categoriesApi.getAll(page),
    placeholderData: keepPreviousData,
  });
}

export const categoryKeys = {
  all: ['categories'] as const,
  lists: () => [...categoryKeys.all, 'list'] as const,
  list: (filters: { page?: number }) =>
    [...categoryKeys.lists(), filters] as const,
};