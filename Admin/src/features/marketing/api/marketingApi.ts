import httpClient from '@/shared/api/httpClient';
import type { PaginatedResponse, ApiResponse } from '@/shared/types/api';
import type { Subscriber, ContactMessage, SendNewsletterPayload } from '../types/marketing';

export const marketingApi = {
  // Subscribers
  getSubscribers: async (page = 1): Promise<PaginatedResponse<Subscriber>> => {
    const res = await httpClient.get<PaginatedResponse<Subscriber>>('/admin/subscribers', { params: { page } });
    return res.data;
  },
  deleteSubscriber: async (id: string): Promise<void> => {
    await httpClient.delete(`/admin/subscribers/${id}`);
  },
  sendNewsletter: async (payload: SendNewsletterPayload): Promise<ApiResponse<null>> => {
    const res = await httpClient.post<ApiResponse<null>>('/admin/newsletter/send', payload);
    return res.data;
  },

  // Contact Messages
  getContactMessages: async (page = 1): Promise<PaginatedResponse<ContactMessage>> => {
    const res = await httpClient.get<PaginatedResponse<ContactMessage>>('/admin/contact-messages', { params: { page } });
    return res.data;
  },
  deleteContactMessage: async (id: string): Promise<void> => {
    await httpClient.delete(`/admin/contact-messages/${id}`);
  },

  toggleSubscriberStatus: async (id: string): Promise<void> => {
    await httpClient.patch(`/admin/subscribers/${id}/toggle-status`);
  },
  toggleMessageReadStatus: async (id: string): Promise<void> => {
    await httpClient.patch(`/admin/contact-messages/${id}/toggle-read`);
  },
  replyToMessage: async (id: string, replyBody: string): Promise<ApiResponse<null>> => {
    const res = await httpClient.post<ApiResponse<null>>(`/admin/contact-messages/${id}/reply`, { reply_body: replyBody });
    return res.data;
  },
};