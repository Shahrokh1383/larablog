import httpClient from '@/shared/api/httpClient';
import { endpoints } from '@/shared/api/endpoints';
import type { PaginatedResponse } from '@/shared/types/api';
import type { Tag } from '../types/tag';

export const tagsApi = {
  // Added 'page' to the params type definition
  getAll: async (params?: { search?: string; per_page?: number; page?: number }): Promise<PaginatedResponse<Tag>> => {
    const response = await httpClient.get(endpoints.content.tags, { params });
    return response.data; // Returns { data: Tag[], meta: {...} }
  },
  
  getPopular: async (): Promise<Tag[]> => {
    const response = await httpClient.get(endpoints.content.popularTags);
    return response.data.data || response.data; 
  },
};