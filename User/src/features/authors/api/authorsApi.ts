import httpClient from '@/shared/api/httpClient';
import type { Author } from '../types/author';
import type { PaginatedResponse } from '@/shared/types/api';

export const authorsApi = {
  getAll: async (params?: { search?: string; per_page?: number; page?: number }): Promise<PaginatedResponse<Author>> => {
    const response = await httpClient.get('/authors', { params });
    return response.data;
  },
};