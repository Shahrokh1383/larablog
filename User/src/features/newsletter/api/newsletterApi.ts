import httpClient from '@/shared/api/httpClient';
import type { SubscribePayload, SubscribeResponse } from '../types/newsletter';

export const newsletterApi = {
  subscribe: async (payload: SubscribePayload): Promise<SubscribeResponse> => {
    const response = await httpClient.post<SubscribeResponse>('/newsletter/subscribe', payload);
    return response.data;
  },
};