import { useQuery } from '@tanstack/react-query';
import { readerApi } from '../api/readerApi';

export const readerKeys = {
  all: ['reader'] as const,
  savedPosts: () => [...readerKeys.all, 'saved-posts'] as const,
};

interface UseSavedPostsOptions {
  enabled?: boolean;
}

export function useSavedPosts(page: number = 1, options?: UseSavedPostsOptions) {
  return useQuery({
    queryKey: [...readerKeys.savedPosts(), page],
    queryFn: () => readerApi.getSavedPosts(page),
    staleTime: 1000 * 60 * 5,
    gcTime: 1000 * 60 * 30,
    enabled: options?.enabled ?? true,
  });
}