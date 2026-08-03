import httpClient from '@/shared/api/httpClient';
import { endpoints } from '@/shared/api/endpoints';
import type { PaginatedResponse } from '@/shared/types/api';
import type { Category } from '../types/category';
import type { Post } from '@/features/posts/types/post';

export interface CategoryPostsResponse {
  category: Category;
  posts: PaginatedResponse<Post>;
}

export const categoriesApi = {
  getAll: async (params?: { search?: string; per_page?: number; page?: number }): Promise<PaginatedResponse<Category>> => {
    const response = await httpClient.get(endpoints.content.categories, { params });
    return response.data;
  },

  getPostsByCategory: async (slug: string, params: { sort: string; page: number; per_page: number }): Promise<CategoryPostsResponse> => {
    const response = await httpClient.get(endpoints.content.categoryPosts(slug), { params });
    return response.data;
  },
};