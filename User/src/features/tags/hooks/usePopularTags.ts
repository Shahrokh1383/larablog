import { useQuery } from '@tanstack/react-query';
import { tagsApi } from '../api/tagsApi';
import { tagKeys } from './useTags';

export function usePopularTags() {
  return useQuery({
    queryKey: tagKeys.popular(),
    queryFn: tagsApi.getPopular,
  });
}