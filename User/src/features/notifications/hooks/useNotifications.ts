import { useEffect } from 'react';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import echo from '@/shared/lib/reverb';
import { useAuth } from '@/features/auth/context/AuthContext';
import { notificationsApi, NotificationItem } from '../api/notificationsApi';

export const notificationKeys = {
  all: ['notifications'] as const,
  unread: () => [...notificationKeys.all, 'unread'] as const,
};

export function useNotifications() {
  const { user, isAuthenticated } = useAuth();
  const queryClient = useQueryClient();

  const { data: notifications = [], isLoading } = useQuery({
    queryKey: notificationKeys.unread(),
    queryFn: notificationsApi.getUnread,
    enabled: isAuthenticated && !!user,
  });

  const markSingleAsReadMutation = useMutation({
    mutationFn: notificationsApi.markSingleAsRead,
    onSuccess: (_, id) => {
      // Evict the specific notification from the cache instantly
      queryClient.setQueryData<NotificationItem[]>(notificationKeys.unread(), (old = []) => 
        old.filter(notif => notif.id !== id)
      );
    },
  });

  const markAllAsReadMutation = useMutation({
    mutationFn: notificationsApi.markAllAsRead,
    onSuccess: () => {
      // Clear the entire cache array instantly
      queryClient.setQueryData<NotificationItem[]>(notificationKeys.unread(), []);
    },
  });

  useEffect(() => {
    if (!echo || !isAuthenticated || !user?.id) return;

    const userId = String(user.id);
    const channelName = `users.${userId}`;
    const channel = echo.private(channelName);

    channel.listen('.notification', (notification: NotificationItem) => {
      queryClient.setQueryData<NotificationItem[]>(notificationKeys.unread(), (old = []) => {
        if (old.some(n => n.id === notification.id)) return old;
        return [notification, ...old].slice(0, 10);
      });
    });

    return () => {
      if (echo) {
        echo.leaveChannel(channelName);
      }
    };
  }, [isAuthenticated, user?.id, queryClient]);

  // Since we only fetch unread, the count is simply the array length
  const unreadCount = notifications.length;

  return {
    notifications,
    unreadCount,
    markSingleAsRead: markSingleAsReadMutation.mutate,
    markAllAsRead: markAllAsReadMutation.mutate,
    isLoading,
  };
}