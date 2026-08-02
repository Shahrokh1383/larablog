import { useQuery } from '@tanstack/react-query';
import { categoriesApi } from '../api/categoriesApi';

export const categoryKeys = {
  all: ['categories'] as const,
  lists: () => [...categoryKeys.all, 'list'] as const,
};

export function useCategories(search?: string) {
  return useQuery({
    queryKey: [...categoryKeys.lists(), search],
    queryFn: () => categoriesApi.getAll({ search, per_page: 10 }),
  });
}