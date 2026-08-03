import { useState } from 'react';
import { useQuery } from '@tanstack/react-query';
import { tagsApi } from '../api/tagsApi';
import { useDebounce } from '@/shared/hooks/useDebounce';

export const tagKeys = {
  all: ['tags'] as const,
  lists: () => [...tagKeys.all, 'list'] as const,
  list: (search: string) => [...tagKeys.lists(), search] as const,
  popular: () => [...tagKeys.all, 'popular'] as const,
};

export function useTags() {
  const [search, setSearch] = useState('');
  const debouncedSearch = useDebounce(search, 300);

  const query = useQuery({
    queryKey: tagKeys.list(debouncedSearch),
    // Changed per_page to 50 to comply with backend FormRequest validation (max:50)
    queryFn: () => tagsApi.getAll({ search: debouncedSearch, per_page: 50 }),
    placeholderData: (previousData) => previousData,
  });

  return {
    search,
    setSearch,
    tags: query.data?.data || [],
    isLoading: query.isLoading,
    isError: query.isError,
  };
}