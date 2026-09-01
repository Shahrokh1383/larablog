import httpClient from '@/shared/api/httpClient';
import type { AuthorStats, DashboardStats, TopCommenter } from '../types/dashboard';
import type { ApiResponse } from '@/shared/types/api';

export const dashboardApi = {
  getStats: async (): Promise<DashboardStats> => {
    const response = await httpClient.get<ApiResponse<DashboardStats>>('/admin/stats/dashboard');
    return response.data.data;
  },

  getMyStats: async (): Promise<AuthorStats> => {
    const response = await httpClient.get<ApiResponse<AuthorStats>>('/admin/stats/me');
    return response.data.data;
  },

  getTopCommenters: async (): Promise<TopCommenter[]> => {
    const response = await httpClient.get<ApiResponse<TopCommenter[]>>('/admin/stats/commenters');
    return response.data.data;
  },
};