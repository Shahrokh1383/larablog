import httpClient from '@/shared/api/httpClient';
import type { Tag, TagFormData } from '../types/tag';
import type { PaginatedResponse, ApiResponse } from '@/shared/types/api';

export const tagsApi = {
  getAll: async (page = 1): Promise<PaginatedResponse<Tag>> => {
    const response = await httpClient.get<PaginatedResponse<Tag>>('/admin/tags', {
      params: { page },
    });
    return response.data;
  },
  create: async (data: TagFormData): Promise<Tag> => {
    const response = await httpClient.post<ApiResponse<Tag>>('/admin/tags', data);
    return response.data.data;
  },
  update: async (id: string, data: TagFormData): Promise<Tag> => {
    const response = await httpClient.put<ApiResponse<Tag>>(`/admin/tags/${id}`, data);
    return response.data.data;
  },
  delete: async (id: string): Promise<void> => {
    await httpClient.delete(`/admin/tags/${id}`);
  },
};