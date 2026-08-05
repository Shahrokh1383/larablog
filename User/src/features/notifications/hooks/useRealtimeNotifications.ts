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
    if (!echo || !isAuthenticated || !user || !user.id) return;

    const userId = String(user.id);
    const channelName = `users.${userId}`;
    
    const channel = echo.private(channelName);

    channel.notification((notification: RealtimeNotification) => {
      setNotifications((prev) => [notification, ...prev]);
      setUnreadCount((prev) => prev + 1);
    });

    return () => {
      if (echo) {
        echo.leaveChannel(channelName);
      }
    };
  }, [isAuthenticated, user]);

  const clearUnread = () => setUnreadCount(0);

  return { notifications, unreadCount, clearUnread };
}