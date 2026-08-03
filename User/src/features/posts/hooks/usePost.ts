import { useQuery } from '@tanstack/react-query';
import { postsApi } from '../api/postsApi';

export const postKeys = {
  all: ['posts'] as const,
  details: () => [...postKeys.all, 'detail'] as const,
  detail: (slug: string) => [...postKeys.details(), slug] as const,
  related: (slug: string) => [...postKeys.all, 'related', slug] as const,
};

export function usePost(slug: string) {
  return useQuery({
    queryKey: postKeys.detail(slug),
    queryFn: () => postsApi.getBySlug(slug),
    enabled: !!slug,
  });
}