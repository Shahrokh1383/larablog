import httpClient from '@/shared/api/httpClient';

export interface ContactPayload {
  name?: string;
  email?: string;
  subject: string;
  message: string;
}

export const contactApi = {
  submit: async (payload: ContactPayload): Promise<{ message: string }> => {
    const res = await httpClient.post<{ message: string }>('/contact', payload);
    return res.data;
  },
};