import { useQuery } from '@tanstack/react-query';
import { tagsApi } from '../api/tagsApi';
import { tagKeys } from './useTags';
import type { Tag } from '../types/tag';

export function usePopularTags() {
  return useQuery<Tag[]>({
    queryKey: tagKeys.popular(),
    queryFn: tagsApi.getPopular,
  });
}