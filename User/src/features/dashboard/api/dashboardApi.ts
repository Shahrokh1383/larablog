import httpClient from '@/shared/api/httpClient';
import { ApiResponse, PaginatedResponse } from '@/shared/types/api';

export interface DashboardOverview {
  posts_read_count: number;
  total_reading_time: number;
  comments_count: number;
  is_top_commenter: boolean;
  total_comments: number;
  total_saved_posts: number;
}

export interface RecentlyReadItem {
  id: string;
  read_at: string;
  post: {
    id: string;
    title: string;
    slug: string;
    featured_image: string;
    reading_time: number;
  };
}

export const dashboardApi = {
  getOverview: async (): Promise<DashboardOverview> => {
    const res = await httpClient.get<ApiResponse<DashboardOverview>>('/dashboard/overview');
    return res.data.data;
  },

  getRecentlyRead: async (page = 1): Promise<PaginatedResponse<RecentlyReadItem>> => {
    const res = await httpClient.get<PaginatedResponse<RecentlyReadItem>>('/dashboard/recently-read', {
      params: { page },
    });
    return res.data;
  },
};