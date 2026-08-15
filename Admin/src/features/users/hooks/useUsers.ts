import { useQuery } from '@tanstack/react-query';
import { usersApi } from '../api/usersApi';

export interface UserFilters {
  page: number;
  search: string;
  per_page?: number;
}

export const userKeys = {
  all: ['users'] as const,
  lists: () => [...userKeys.all, 'list'] as const,
  list: (filters: UserFilters) => [...userKeys.lists(), filters] as const,
  details: () => [...userKeys.all, 'detail'] as const,
  detail: (id: string) => [...userKeys.details(), id] as const,
};

export function useUsers(page = 1, search = '') {
  const filters: UserFilters = { page, search };

  return useQuery({
    queryKey: userKeys.list(filters),
    queryFn: () => usersApi.getAll(page, search),
  });
}