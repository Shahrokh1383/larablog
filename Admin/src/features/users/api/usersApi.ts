import httpClient from '@/shared/api/httpClient';
import type { AdminUser, UpdateRolePayload, UpdatePasswordPayload } from '../types/user';
import type { PaginatedResponse } from '@/shared/types/api';

export const usersApi = {
  getAll: async (page = 1, search = ''): Promise<PaginatedResponse<AdminUser>> => {
    const response = await httpClient.get('/admin/users', { 
      params: { page, search } 
    });
    return response.data; 
  },

  updateRole: async (userId: string, payload: UpdateRolePayload): Promise<{ message: string, user: AdminUser }> => {
    const response = await httpClient.put(`/admin/users/${userId}/role`, payload);
    return response.data;
  },

  updatePassword: async (userId: string, payload: UpdatePasswordPayload): Promise<{ message: string }> => {
    const response = await httpClient.put(`/admin/users/${userId}/password`, payload);
    return response.data;
  },
};