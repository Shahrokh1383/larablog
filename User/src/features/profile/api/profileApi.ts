import httpClient from '@/shared/api/httpClient';
import { ApiResponse } from '@/shared/types/api';

export interface Profile {
  id: string;
  user_id: string;
  name: string;
  username: string;
  avatar: string | null;
  bio: string | null;
  expertise: string | null;
  years_of_experience: number | null;
  social_links: Record<string, string>;
  posts_count: number;
  total_views: number;
  created_at: string;
  updated_at: string;
}

export interface UpdateProfilePayload {
  name: string;
  avatar?: string | null;
  bio?: string | null;
}

export const profileApi = {
  getProfile: async (): Promise<Profile> => {
    const res = await httpClient.get<ApiResponse<Profile>>('/profile');
    return res.data.data;
  },

  updateProfile: async (data: UpdateProfilePayload): Promise<Profile> => {
    const res = await httpClient.put<ApiResponse<Profile>>('/profile', data);
    return res.data.data;
  },

  uploadAvatar: async (file: File): Promise<{ url: string }> => {
    const formData = new FormData();
    formData.append('avatar', file);
    const res = await httpClient.post<{ url: string }>('/profile/upload-avatar', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });
    return res.data;
  },

  deleteAvatar: async (url: string): Promise<void> => {
    await httpClient.delete('/profile/delete-avatar', { data: { url } });
  },

  deleteAccount: async (): Promise<void> => {
    await httpClient.delete('/profile');
  },
};