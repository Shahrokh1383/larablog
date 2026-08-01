import httpClient from '@/shared/api/httpClient';
import type { Post, PostFormData } from '../types/post';

export const postsApi = {
  getAll: async (): Promise<Post[]> => {
    const response = await httpClient.get('/admin/posts');
    return response.data.data;
  },

  getById: async (id: string): Promise<Post> => {
    const response = await httpClient.get(`/admin/posts/${id}`);
    return response.data.data;
  },

  create: async (data: PostFormData): Promise<Post> => {
    const response = await httpClient.post('/admin/posts', data);
    return response.data.data;
  },

  update: async (id: string, data: Partial<PostFormData>): Promise<Post> => {
    const response = await httpClient.put(`/admin/posts/${id}`, data);
    return response.data.data;
  },

  delete: async (id: string): Promise<void> => {
    await httpClient.delete(`/admin/posts/${id}`);
  },
};