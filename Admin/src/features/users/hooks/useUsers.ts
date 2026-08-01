import { useQuery } from '@tanstack/react-query';
import { usersApi } from '../api/usersApi';

export function useUsers(page = 1, search = '') {
  return useQuery({
    queryKey: ['users', 'list', page, search],
    queryFn: () => usersApi.getAll(page, search),
  });
}

export const userKeys = {
  all: ['users'] as const,
  lists: () => [...userKeys.all, 'list'] as const,
};