import { useEffect, useState } from 'react';
import echo from '@/shared/lib/reverb';
import { useAuth } from '@/features/auth/context/AuthContext';

export interface RealtimeNotification {
  type: string;
  message: string;
  post_id: string;
  comment_id?: string;
  reply_id?: string;
  created_at: string;
}

export function useRealtimeNotifications() {
  const { user, isAuthenticated } = useAuth();
  const [notifications, setNotifications] = useState<RealtimeNotification[]>([]);
  const [unreadCount, setUnreadCount] = useState(0);

  useEffect(() => {
    if (!isAuthenticated || !user) return;

    const channel = echo.private(`users.${user.id}`);

    channel.notification((notification: RealtimeNotification) => {
      setNotifications((prev) => [notification, ...prev]);
      setUnreadCount((prev) => prev + 1);
    });

    return () => {
      echo.leaveChannel(`users.${user.id}`);
    };
  }, [isAuthenticated, user]);

  const clearUnread = () => setUnreadCount(0);

  return { notifications, unreadCount, clearUnread };
}