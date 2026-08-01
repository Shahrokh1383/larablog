import { useQuery } from '@tanstack/react-query';
import { categoriesApi } from '../api/categoriesApi';
import { categoryKeys } from '../index';

export function useCategories() {
  return useQuery({
    queryKey: categoryKeys.lists(),
    queryFn: categoriesApi.getAll,
  });
}