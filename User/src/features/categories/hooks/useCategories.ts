import { useQuery } from '@tanstack/react-query';
import { categoriesApi } from '../api/categoriesApi';
import type { PaginatedResponse } from '@/shared/types/api';
import type { Category } from '../types/category';

export const categoryKeys = {
  all: ['categories'] as const,
  lists: () => [...categoryKeys.all, 'list'] as const,
  list: (filters: { search?: string; page?: number; per_page?: number }) => [...categoryKeys.lists(), filters] as const,
};

interface UseCategoriesOptions {
  search?: string;
  page?: number;
  per_page?: number;
}

export function useCategories(options: UseCategoriesOptions = {}) {
  const { search, page = 1, per_page = 10 } = options;

  return useQuery<PaginatedResponse<Category>>({
    queryKey: categoryKeys.list({ search, page, per_page }),
    queryFn: () => categoriesApi.getAll({ search, page, per_page }),
    enabled: per_page > 0,
    placeholderData: (previousData) => previousData,
  });
}