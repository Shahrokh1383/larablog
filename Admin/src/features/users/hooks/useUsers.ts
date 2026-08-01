import { useQuery } from '@tanstack/react-query';
import { usersApi } from '../api/usersApi';

export function useUsers(page = 1) {
  return useQuery({
    queryKey: ['users', 'list', page],
    queryFn: () => usersApi.getAll(page),
  });
}

export const userKeys = {
  all: ['users'] as const,
  lists: () => [...userKeys.all, 'list'] as const,
};