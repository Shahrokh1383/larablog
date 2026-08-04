import httpClient from '@/shared/api/httpClient';
import { endpoints } from '@/shared/api/endpoints';
import type { PaginatedResponse } from '@/shared/types/api';
import type { Tag } from '../types/tag';
import type { Post } from '@/features/posts/types/post';

export interface TagPostsResponse {
  tag: Tag;
  posts: PaginatedResponse<Post>;
}

export const tagsApi = {
  getAll: async (params?: { search?: string; per_page?: number; page?: number }): Promise<PaginatedResponse<Tag>> => {
    const response = await httpClient.get(endpoints.content.tags, { params });
    return response.data;
  },
  
  getPopular: async (): Promise<Tag[]> => {
    const response = await httpClient.get(endpoints.content.popularTags);
    return response.data.data || response.data; 
  },

  getPostsByTag: async (slug: string, params: { sort: string; page: number; per_page: number }): Promise<TagPostsResponse> => {
    const response = await httpClient.get(endpoints.content.tagPosts(slug), { params });
    return response.data;
  },
};