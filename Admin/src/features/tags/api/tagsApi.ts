import httpClient from '@/shared/api/httpClient';
import type { Tag, TagFormData } from '../types/tag';

export const tagsApi = {
  getAll: async (): Promise<Tag[]> => {
    const response = await httpClient.get('/admin/tags');
    return response.data.data;
  },
  create: async (data: TagFormData): Promise<Tag> => {
    const response = await httpClient.post('/admin/tags', data);
    return response.data.data;
  },
  update: async (id: string, data: TagFormData): Promise<Tag> => {
    const response = await httpClient.put(`/admin/tags/${id}`, data);
    return response.data.data;
  },
  delete: async (id: string): Promise<void> => {
    await httpClient.delete(`/admin/tags/${id}`);
  },
};