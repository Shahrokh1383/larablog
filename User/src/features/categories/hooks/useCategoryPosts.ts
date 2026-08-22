import { useQuery } from '@tanstack/react-query';
import { categoriesApi, CategoryPostsResponse } from '../api/categoriesApi';

export const categoryPostKeys = {
  all: ['categoryPosts'] as const,
  detail: (slug: string, sort: string, page: number, per_page: number) => 
    [...categoryPostKeys.all, slug, sort, page, per_page] as const,
};

export function useCategoryPosts(slug: string, sort: string = 'newest', page: number = 1, per_page: number = 10) {
  return useQuery<CategoryPostsResponse>({
    queryKey: categoryPostKeys.detail(slug, sort, page, per_page),
    queryFn: () => categoriesApi.getPostsByCategory(slug, { sort, page, per_page }),
    enabled: !!slug,
  });
}