import httpClient from '@/shared/api/httpClient';
import type { DashboardStats } from '../types/dashboard';
import type { ApiResponse } from '@/shared/types/api';

export const dashboardApi = {
  getStats: async (): Promise<DashboardStats> => {
    const response = await httpClient.get<ApiResponse<DashboardStats>>('/admin/dashboard/stats');
    return response.data.data;
  },
};