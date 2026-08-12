import httpClient from '@/shared/api/httpClient';
import { AboutData } from '../types/about';

export const aboutApi = {
  getAboutData: async (page = 1): Promise<AboutData> => {
    const response = await httpClient.get<{ data: AboutData }>('/about', {
      params: { page }
    });
    return response.data.data;
  },
};