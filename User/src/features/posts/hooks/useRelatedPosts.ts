import { useQuery } from '@tanstack/react-query';
import { postsApi } from '../api/postsApi';
import { postKeys } from './usePost';

export function useRelatedPosts(slug: string) {
  return useQuery({
    queryKey: postKeys.related(slug),
    queryFn: () => postsApi.getRelated(slug),
    enabled: !!slug,
  });
}