import httpClient from '@/shared/api/httpClient';
import { SearchResponse } from '../types/search';

export const searchApi = {
  globalSearch: async (q: string, page: number = 1): Promise<SearchResponse> => {
    const response = await httpClient.get('/search', { params: { q, page } });
    return response.data;
  },
};