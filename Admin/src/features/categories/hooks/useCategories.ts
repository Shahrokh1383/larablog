import { useQuery, keepPreviousData } from '@tanstack/react-query';
import { categoriesApi } from '../api/categoriesApi';
import { categoryKeys } from '../index';
import type { PaginatedResponse } from '@/shared/types/api';
import type { Category } from '../types/category';

export function useCategories(options?: { page?: number; perPage?: number }) {
  const { page = 1, perPage } = options ?? {};

  return useQuery<PaginatedResponse<Category>>({
    queryKey: categoryKeys.list({ page, perPage }),
    queryFn: () => categoriesApi.getAll({ page, perPage }),
    placeholderData: keepPreviousData,
  });
}