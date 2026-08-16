import { useQuery } from '@tanstack/react-query';
import { postsApi } from '../api/postsApi';
import { postKeys } from '../index';

export function usePostDetail(id?: string) {
  return useQuery({
    queryKey: postKeys.detail(id!),
    queryFn: () => postsApi.getById(id!),
    enabled: !!id,
  });
}