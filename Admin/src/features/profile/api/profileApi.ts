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
};