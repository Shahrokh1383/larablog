import { useState } from 'react';
import { useQuery } from '@tanstack/react-query';
import { authorsApi } from '../api/authorsApi';
import { useDebounce } from '@/shared/hooks/useDebounce';

export const authorKeys = {
  all: ['authors'] as const,
  lists: () => [...authorKeys.all, 'list'] as const,
  list: (search: string, page: number) => [...authorKeys.lists(), search, page] as const,
};

export function useAuthors() {
  const [search, setSearch] = useState('');
  const [page, setPage] = useState(1);
  const debouncedSearch = useDebounce(search, 300);

  const query = useQuery({
    queryKey: authorKeys.list(debouncedSearch, page),
    queryFn: () => authorsApi.getAll({ search: debouncedSearch, page, per_page: 12 }),
    placeholderData: (previousData) => previousData,
  });

  return {
    search,
    setSearch,
    page,
    setPage,
    authors: query.data?.data || [],
    totalPages: query.data?.meta?.last_page || 1,
    isLoading: query.isLoading,
    isError: query.isError,
  };
}