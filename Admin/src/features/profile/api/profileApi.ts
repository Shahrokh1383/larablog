import httpClient from '@/shared/api/httpClient';
import { Profile, UpdateProfilePayload } from '../types/profile';

export const profileApi = {
  get: async (): Promise<Profile> => {
    const response = await httpClient.get<any>('/profile');
    return response.data.data ?? response.data;
  },
  
  update: async (payload: UpdateProfilePayload): Promise<Profile> => {
    const response = await httpClient.put<any>('/profile', payload);
    return response.data.data ?? response.data;
  },

  uploadAvatar: async (file: File): Promise<string> => {
    const formData = new FormData();
    formData.append('avatar', file);
    const response = await httpClient.post<{ url: string }>('/profile/upload-avatar', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });
    return response.data.url;
  },

  deleteAvatar: async (url: string): Promise<void> => {
    await httpClient.delete('/profile/delete-avatar', {
      data: { url },
    });
  },
};