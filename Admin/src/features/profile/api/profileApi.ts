import httpClient from '@/shared/api/httpClient';
import { Profile, UpdateProfilePayload } from '../types/profile';

export const profileApi = {
  get: async (): Promise<Profile> => {
    const response = await httpClient.get<Profile>('/profile');
    return response.data;
  },
  
  update: async (payload: UpdateProfilePayload): Promise<Profile> => {
    const response = await httpClient.put<Profile>('/profile', payload);
    return response.data;
  },
};