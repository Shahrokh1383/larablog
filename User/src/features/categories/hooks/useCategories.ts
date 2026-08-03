import { useState } from 'react';
import { useQuery } from '@tanstack/react-query';
import { categoriesApi } from '../api/categoriesApi';
import { useDebounce } from '@/shared/hooks/useDebounce';

export const categoryKeys = {
  all: ['categories'] as const,
  lists: () => [...categoryKeys.all, 'list'] as const,
  list: (search: string, page: number) => [...categoryKeys.lists(), search, page] as const,
};

// Hook for the main Category Index page (with search and pagination)
export function useCategoryList() {
  const [search, setSearch] = useState('');
  const [page, setPage] = useState(1);
  const debouncedSearch = useDebounce(search, 300);

  const query = useQuery({
    queryKey: categoryKeys.list(debouncedSearch, page),
    queryFn: () => categoriesApi.getAll({ search: debouncedSearch, page, per_page: 12 }),
    placeholderData: (previousData) => previousData,
  });

  return {
    search,
    setSearch,
    page,
    setPage,
    categories: query.data?.data || [],
    totalPages: query.data?.meta?.last_page || 1,
    isLoading: query.isLoading,
    isError: query.isError,
  };
}

// Hook for the Sidebar (fetches a larger list without pagination state)
export function useCategories() {
  const query = useQuery({
    queryKey: [...categoryKeys.lists(), 'sidebar'],
    queryFn: () => categoriesApi.getAll({ per_page: 50 }),
  });
  
  // Extract the array so the sidebar doesn't receive a paginated object
  return {
    ...query,
    data: query.data?.data || [],
  };
}