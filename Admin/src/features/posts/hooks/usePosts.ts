import { useQuery } from '@tanstack/react-query';
import { postsApi } from '../api/postsApi';
import { postKeys } from '../index';

export function usePosts(search?: string) {
  return useQuery({
    queryKey: search ? [...postKeys.lists(), { search }] : postKeys.lists(),
    queryFn: () => postsApi.getAll(search),
  });
}