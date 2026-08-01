import { useQuery } from '@tanstack/react-query';
import { tagsApi } from '../api/tagsApi';
import { tagKeys } from '../index';

export function useTags() {
  return useQuery({
    queryKey: tagKeys.lists(),
    queryFn: tagsApi.getAll,
  });
}