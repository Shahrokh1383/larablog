import { useQuery } from '@tanstack/react-query';
import { tagsApi } from '../api/tagsApi';

export const tagKeys = {
  all: ['tags'] as const,
  lists: () => [...tagKeys.all, 'list'] as const,
  list: (search: string, page: number) => [...tagKeys.lists(), search, page] as const,
  popular: () => [...tagKeys.all, 'popular'] as const,
};

export function useTags(search: string = '', page: number = 1) {
  return useQuery({
    queryKey: tagKeys.list(search, page),
    queryFn: () => tagsApi.getAll({ search, per_page: 12, page }),
  });
}