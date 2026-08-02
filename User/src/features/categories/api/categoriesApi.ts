import httpClient from '@/shared/api/httpClient';
import { endpoints } from '@/shared/api/endpoints';

export const categoriesApi = {
  getAll: async (params?: Record<string, any>) => {
    const response = await httpClient.get(endpoints.content.categories, { params });
    return response.data;
  },

  getPostsByCategory: async (slug: string, params: { sort: string; page: number; per_page: number }) => {
    const response = await httpClient.get(endpoints.content.categoryPosts(slug), { params });
    return response.data;
  },
};