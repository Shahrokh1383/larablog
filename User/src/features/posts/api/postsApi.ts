import httpClient from '@/shared/api/httpClient';
import type { Post, Category, Tag } from '../types/post';
import type { PaginatedResponse, ApiResponse } from '@/shared/types/api';

export const postsApi = {
  getAll: async (page = 1, perPage = 10): Promise<PaginatedResponse<Post>> => {
    const response = await httpClient.get<PaginatedResponse<Post>>('/posts', {
      params: { page, per_page: perPage },
    });
    return response.data;
  },

  getBySlug: async (slug: string): Promise<Post> => {
    const response = await httpClient.get<ApiResponse<Post>>(`/posts/${slug}`);
    return response.data.data;
  },

  getRelated: async (slug: string): Promise<Post[]> => {
    const response = await httpClient.get<ApiResponse<Post[]>>(`/posts/${slug}/related`);
    return response.data.data;
  },

  getByCategory: async (categorySlug: string, sort = 'newest', page = 1, perPage = 10) => {
    const response = await httpClient.get<{
      category: Category;
      posts: PaginatedResponse<Post>;
    }>(`/categories/${categorySlug}/posts`, {
      params: { sort, page, per_page: perPage },
    });
    return response.data;
  },

  getByTag: async (tagSlug: string, sort = 'newest', page = 1, perPage = 10) => {
    const response = await httpClient.get<{
      tag: Tag;
      posts: PaginatedResponse<Post>;
    }>(`/tags/${tagSlug}/posts`, {
      params: { sort, page, per_page: perPage },
    });
    return response.data;
  },
};