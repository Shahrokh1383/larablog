import { useQuery } from '@tanstack/react-query';
import { readerApi } from '../api/readerApi';

export const readerKeys = {
  all: ['reader'] as const,
  savedPosts: () => [...readerKeys.all, 'saved-posts'] as const,
};

export function useSavedPosts(page = 1) {
  return useQuery({
    queryKey: [...readerKeys.savedPosts(), page],
    queryFn: () => readerApi.getSavedPosts(page),
  });
}