import { useEffect } from 'react';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import echo from '@/shared/lib/reverb';
import { useAuth } from '@/features/auth/context/AuthContext';
import { notificationsApi, NotificationItem } from '../api/notificationsApi';

export const notificationKeys = {
  all: ['notifications'] as const,
  recent: () => [...notificationKeys.all, 'recent'] as const,
};

export function useNotifications() {
  const { user, isAuthenticated } = useAuth();
  const queryClient = useQueryClient();

  const { data: notifications = [], isLoading } = useQuery({
    queryKey: notificationKeys.recent(),
    queryFn: notificationsApi.getRecent,
    enabled: isAuthenticated && !!user,
  });

  const markAsReadMutation = useMutation({
    mutationFn: notificationsApi.markAsRead,
    onSuccess: () => {
      queryClient.setQueryData<NotificationItem[]>(notificationKeys.recent(), (old = []) => 
        old.map(notif => notif.read_at ? notif : { ...notif, read_at: new Date().toISOString() })
      );
    },
  });

  useEffect(() => {
    if (!echo || !isAuthenticated || !user?.id) return;

    const userId = String(user.id);
    const channelName = `users.${userId}`;
    const channel = echo.private(channelName);

    channel.listen('.notification', (notification: NotificationItem) => {
      queryClient.setQueryData<NotificationItem[]>(notificationKeys.recent(), (old = []) => {
        // Prevent duplicate inserts if the event fires twice
        if (old.some(n => n.id === notification.id)) return old;
        return [notification, ...old].slice(0, 10); // Keep max 10 items
      });
    });

    return () => {
      if (echo) {
        echo.leaveChannel(channelName);
      }
    };
  }, [isAuthenticated, user?.id, queryClient]);

  // Dynamically calculate unread count based on read_at property
  const unreadCount = notifications.filter(n => !n.read_at).length;

  return {
    notifications,
    unreadCount,
    markAsRead: markAsReadMutation.mutate,
    isLoading,
  };
}