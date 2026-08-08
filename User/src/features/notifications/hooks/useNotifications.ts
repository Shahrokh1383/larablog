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

  const markAsReadMutation = useMutation({
    mutationFn: notificationsApi.markAsRead,
    onSuccess: () => {
      queryClient.setQueryData(notificationKeys.unread(), []);
    },
  });

  useEffect(() => {
    if (!echo || !isAuthenticated || !user?.id) return;

    const userId = String(user.id);
    const channelName = `users.${userId}`;
    const channel = echo.private(channelName);

    // Listen specifically for the '.notification' event broadcasted by UserNotificationBroadcast
    channel.listen('.notification', (notification: NotificationItem) => {
      queryClient.setQueryData<NotificationItem[]>(notificationKeys.unread(), (old = []) => [
        notification,
        ...old,
      ]);
    });

    return () => {
      if (echo) {
        echo.leaveChannel(channelName);
      }
    };
  }, [isAuthenticated, user?.id, queryClient]);

  return {
    notifications,
    unreadCount: notifications.length,
    markAsRead: markAsReadMutation.mutate,
    isLoading,
  };
}