import { useQuery } from '@tanstack/react-query';
import { categoriesApi } from '../api/categoriesApi';

export const categoryPostKeys = {
  all: ['categoryPosts'] as const,
  detail: (slug: string, sort: string, page: number) => [...categoryPostKeys.all, slug, sort, page] as const,
};

export function useCategoryPosts(slug: string, sort: string = 'newest', page: number = 1) {
  return useQuery({
    queryKey: categoryPostKeys.detail(slug, sort, page),
    queryFn: () => categoriesApi.getPostsByCategory(slug, { sort, page, per_page: 4 }),
    enabled: !!slug,
  });
}