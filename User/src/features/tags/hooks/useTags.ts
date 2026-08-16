import { useState } from 'react';
import { useQuery } from '@tanstack/react-query';
import { tagsApi } from '../api/tagsApi';
import { useDebounce } from '@/shared/hooks/useDebounce';

export const tagKeys = {
  all: ['tags'] as const,
  lists: () => [...tagKeys.all, 'list'] as const,
  list: (filters: { search?: string; page?: number }) => [...tagKeys.lists(), filters] as const,
  popular: () => [...tagKeys.all, 'popular'] as const,
};

export function useTags() {
  const [search, setSearch] = useState('');
  const [page, setPage] = useState(1);
  const debouncedSearch = useDebounce(search, 300);

  const query = useQuery({
    queryKey: tagKeys.list({ search: debouncedSearch, page }),
    queryFn: () => tagsApi.getAll({ search: debouncedSearch, page, per_page: 12 }),
    placeholderData: (previousData) => previousData,
  });

  return {
    search, setSearch, page, setPage,
    tags: query.data?.data || [],
    totalPages: query.data?.meta?.last_page || 1,
    isLoading: query.isLoading, isError: query.isError,
  };
}