import httpClient from '@/shared/api/httpClient';
import { Post } from '../types/post';

export const postsApi = {
  getBySlug: async (slug: string): Promise<Post> => {
    const response = await httpClient.get(`/posts/${slug}`);
    return response.data.data;
  },

  getRelated: async (slug: string): Promise<Post[]> => {
    const response = await httpClient.get(`/posts/${slug}/related`);
    return response.data.data;
  },
};