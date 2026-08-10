import httpClient from '@/shared/api/httpClient';
import type { ContactPayload, ContactResponse } from '../types/contact';

export const contactApi = {
  submit: async (payload: ContactPayload): Promise<ContactResponse> => {
    const response = await httpClient.post<ContactResponse>('/contact', payload);
    return response.data;
  },
};