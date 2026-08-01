import httpClient from '@/shared/api/httpClient';
import type { Category, CategoryFormData } from '../types/category';

export const categoriesApi = {
  getAll: async (): Promise<Category[]> => {
    const response = await httpClient.get('/admin/categories');
    return response.data.data;
  },

  create: async (data: CategoryFormData): Promise<Category> => {
    const response = await httpClient.post('/admin/categories', data);
    return response.data.data;
  },

  update: async (id: string, data: CategoryFormData): Promise<Category> => {
    const response = await httpClient.put(`/admin/categories/${id}`, data);
    return response.data.data;
  },

  delete: async (id: string): Promise<void> => {
    await httpClient.delete(`/admin/categories/${id}`);
  },
};