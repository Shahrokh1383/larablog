import httpClient from '@/shared/api/httpClient';
import { ApiResponse, PaginatedResponse } from '@/shared/types/api';

export interface SavedPostItem {
  id: string;
  saved_at: string;
  post: {
    id: string;
    title: string;
    slug: string;
    featured_image: string;
    reading_time: number;
  };
}

export const readerApi = {
  trackRead: async (postId: string): Promise<void> => {
    await httpClient.post(`/reading/posts/${postId}/read`);
  },

  toggleSave: async (postId: string): Promise<{ saved: boolean }> => {
    const res = await httpClient.post<ApiResponse<{ saved: boolean }>>(`/saved-posts/${postId}`);
    return res.data.data;
  },

  getSavedPosts: async (page = 1): Promise<PaginatedResponse<SavedPostItem>> => {
    const res = await httpClient.get<PaginatedResponse<SavedPostItem>>(`/saved-posts`, {
      params: { page },
    });
    return res.data;
  },
};