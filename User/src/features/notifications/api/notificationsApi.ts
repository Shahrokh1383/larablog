import httpClient from '@/shared/api/httpClient';

export interface NotificationData {
  type: string;
  message: string;
  post_id: string;
  comment_id?: string;
  reply_id?: string;
}

export interface NotificationItem {
  id: string;
  type: string;
  data: NotificationData;
  read_at: string | null;
  created_at: string;
}

export const notificationsApi = {
  getUnread: () =>
    httpClient.get<{ data: NotificationItem[] }>('/notifications').then((res) => res.data.data),
  
  markAsRead: () =>
    httpClient.post('/notifications/mark-as-read').then((res) => res.data),
};