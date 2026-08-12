import { useQuery } from '@tanstack/react-query';
import { searchApi } from '../api/searchApi';

export const searchKeys = {
  all: ['search'] as const,
  results: (q: string, page: number) => [...searchKeys.all, q, page] as const,
};

export function useSearchResults(q: string, page: number = 1) {
  return useQuery({
    queryKey: searchKeys.results(q, page),
    queryFn: () => searchApi.globalSearch(q, page),
    enabled: !!q && q.length >= 2, // Prevent fetching on empty/short queries
  });
}