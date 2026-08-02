import { useQuery } from '@tanstack/react-query';
import { tagsApi } from '../api/tagsApi';
import { useDebounce } from '@/shared/hooks/useDebounce';

export const tagKeys = {
  all: ['tags'] as const,
  lists: () => [...tagKeys.all, 'list'] as const,
  list: (search: string, page: number) => [...tagKeys.lists(), search, page] as const,
  popular: () => [...tagKeys.all, 'popular'] as const,
};

export function useTags(search: string, page: number = 1) {
  const debouncedSearch = useDebounce(search, 300);

  return useQuery({
    queryKey: tagKeys.list(debouncedSearch, page),
    queryFn: () => tagsApi.getAll({ search: debouncedSearch, page, per_page: 12 }),
    placeholderData: (previousData) => previousData,
  });
}