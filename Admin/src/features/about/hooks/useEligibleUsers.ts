import { useState } from 'react';
import { useQuery, keepPreviousData } from '@tanstack/react-query';
import { aboutApi } from '../api/aboutApi';
import { useDebounce } from '@/shared/hooks/useDebounce';

export function useEligibleUsers(initialSearch = '') {
  const [search, setSearch] = useState(initialSearch);
  const [page, setPage] = useState(1);
  const debouncedSearch = useDebounce(search, 500);

  const query = useQuery({
    queryKey: ['about', 'eligible-users', debouncedSearch, page],
    queryFn: () => aboutApi.getEligibleUsers(debouncedSearch, page),
    placeholderData: keepPreviousData,
  });

  return {
    ...query,
    search,
    setSearch,
    page,
    setPage,
  };
}