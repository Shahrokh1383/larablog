import httpClient from '@/shared/api/httpClient';
import type { Category, CategoryFormData } from '../types/category';
import type { PaginatedResponse, ApiResponse } from '@/shared/types/api';

export const categoriesApi = {
  getAll: async (params?: { page?: number; perPage?: number }): Promise<PaginatedResponse<Category>> => {
    const response = await httpClient.get<PaginatedResponse<Category>>('/admin/categories', {
      params: {
        page: params?.page ?? 1,
        per_page: params?.perPage ?? 15,
      },
    });
    return response.data;
  },

  create: async (data: CategoryFormData): Promise<Category> => {
    const response = await httpClient.post<ApiResponse<Category>>('/admin/categories', data);
    return response.data.data;
  },

  update: async (id: string, data: CategoryFormData): Promise<Category> => {
    const response = await httpClient.put<ApiResponse<Category>>(`/admin/categories/${id}`, data);
    return response.data.data;
  },

  delete: async (id: string): Promise<void> => {
    await httpClient.delete(`/admin/categories/${id}`);
  },
};