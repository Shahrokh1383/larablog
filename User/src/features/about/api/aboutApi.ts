import httpClient from '@/shared/api/httpClient';
import { AboutData } from '../types/about';

export const aboutApi = {
  getAboutData: async (): Promise<AboutData> => {
    const response = await httpClient.get<{ data: AboutData }>('/about');
    return response.data.data;
  },
};