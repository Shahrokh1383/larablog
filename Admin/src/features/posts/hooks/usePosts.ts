import { useQuery } from '@tanstack/react-query';
import { postsApi } from '../api/postsApi';
import { postKeys } from '../index';

export function usePosts() {
  return useQuery({
    queryKey: postKeys.lists(),
    queryFn: postsApi.getAll,
  });
}