import { useQuery } from '@tanstack/react-query';
import { tagsApi, TagPostsResponse } from '../api/tagsApi';

export const tagPostKeys = {
  all: ['tagPosts'] as const,
  detail: (slug: string, sort: string, page: number) => [...tagPostKeys.all, slug, sort, page] as const,
};

export function useTagPosts(slug: string, sort: string = 'newest', page: number = 1) {
  return useQuery<TagPostsResponse>({
    queryKey: tagPostKeys.detail(slug, sort, page),
    queryFn: () => tagsApi.getPostsByTag(slug, { sort, page, per_page: 9 }),
    enabled: !!slug,
  });
}