import httpClient from '@/shared/api/httpClient';
import type { Author } from '../types/author';
import type { Post } from '@/features/posts/types/post';
import type { PaginatedResponse } from '@/shared/types/api';

export const authorsApi = {
  getAll: async (params?: { search?: string; per_page?: number; page?: number }): Promise<PaginatedResponse<Author>> => {
    const response = await httpClient.get('/authors', { params });
    return response.data;
  },
  getByUsername: async (username: string): Promise<Author> => {
    const response = await httpClient.get(`/authors/${username}`);
    return response.data.data;
  },
  getPosts: async (username: string, params?: { page?: number; per_page?: number }): Promise<PaginatedResponse<Post>> => {
    const response = await httpClient.get(`/authors/${username}/posts`, { params });
    return response.data;
  },
};