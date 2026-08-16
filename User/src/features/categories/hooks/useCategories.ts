import { useState } from 'react';
import { useQuery } from '@tanstack/react-query';
import { categoriesApi } from '../api/categoriesApi';
import { useDebounce } from '@/shared/hooks/useDebounce';

export const categoryKeys = {
  all: ['categories'] as const,
  lists: () => [...categoryKeys.all, 'list'] as const,
  list: (filters: { search?: string; page?: number }) => [...categoryKeys.lists(), filters] as const,
};

export function useCategoryList() {
  const [search, setSearch] = useState('');
  const [page, setPage] = useState(1);
  const debouncedSearch = useDebounce(search, 300);

  const query = useQuery({
    queryKey: categoryKeys.list({ search: debouncedSearch, page }),
    queryFn: () => categoriesApi.getAll({ search: debouncedSearch, page, per_page: 12 }),
    placeholderData: (previousData) => previousData,
  });

  return {
    search, setSearch, page, setPage,
    categories: query.data?.data || [],
    totalPages: query.data?.meta?.last_page || 1,
    isLoading: query.isLoading, isError: query.isError,
  };
}

export function useCategories() {
  const query = useQuery({
    queryKey: [...categoryKeys.lists(), 'sidebar'],
    queryFn: () => categoriesApi.getAll({ per_page: 50 }),
  });
  
  // Explicit, safe return shape
  return {
    categories: query.data?.data || [],
    isLoading: query.isLoading,
    isError: query.isError,
  };
}