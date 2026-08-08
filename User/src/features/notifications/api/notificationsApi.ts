import httpClient from '@/shared/api/httpClient';

export interface NotificationItem {
  id: string;
  type: string;
  message: string;
  post_id: string | null;
  comment_id?: string | null;
  reply_id?: string | null;
  read_at: string | null;
  created_at: string;
}

export const notificationsApi = {
  getRecent: () =>
    httpClient.get<{ data: NotificationItem[] }>('/notifications').then((res) => res.data.data),
  
  markAsRead: () =>
    httpClient.post('/notifications/mark-as-read').then((res) => res.data),
};