import { useQuery } from '@tanstack/react-query';
import { authorsApi } from '../api/authorsApi';

export function useAuthor(username: string) {
  return useQuery({
    queryKey: ['authors', 'detail', username],
    queryFn: () => authorsApi.getByUsername(username),
    enabled: !!username,
  });
}