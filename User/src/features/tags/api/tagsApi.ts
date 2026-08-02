import httpClient from '@/shared/api/httpClient';
import { endpoints } from '@/shared/api/endpoints';

export const tagsApi = {
  getAll: async (params?: { search?: string; per_page?: number; page?: number }) => {
    const response = await httpClient.get(endpoints.content.tags, { params });
    return response.data;
  },
  getPopular: async () => {
    const response = await httpClient.get(endpoints.content.popularTags);
    return response.data;
  },
};