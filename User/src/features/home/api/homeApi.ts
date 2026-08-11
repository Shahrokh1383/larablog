import httpClient from '@/shared/api/httpClient';
import { HomeData } from '../types/home';

export const homeApi = {
  getHomeData: async (): Promise<HomeData> => {
    const { data } = await httpClient.get('/home');
    return data;
  },
};