import { useState } from 'react';
import { useQuery, keepPreviousData } from '@tanstack/react-query';
import { aboutApi } from '../api/aboutApi';
import { useDebounce } from '@/shared/hooks/useDebounce';

export const aboutKeys = {
  all: ['about'] as const,
  eligibleUsers: ['about', 'eligible-users'] as const,
};

export function useEligibleUsers(initialSearch = '') {
  const [search, setSearch] = useState(initialSearch);
  const [page, setPage] = useState(1);
  const debouncedSearch = useDebounce(search, 500);

  const query = useQuery({
    queryKey: [...aboutKeys.eligibleUsers, debouncedSearch, page],
    queryFn: () => aboutApi.getEligibleUsers(debouncedSearch, page, 500),
    placeholderData: keepPreviousData,
    refetchOnMount: 'always',
  });

  return {
    ...query,
    search,
    setSearch,
    page,
    setPage,
  };
}