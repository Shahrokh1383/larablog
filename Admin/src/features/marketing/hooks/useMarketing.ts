import { useQuery, useMutation, useQueryClient, keepPreviousData } from '@tanstack/react-query';
import { marketingApi } from '../api/marketingApi';
import type { Subscriber, ContactMessage, SendNewsletterPayload } from '../types/marketing';
import type { PaginatedResponse } from '@/shared/types/api';

export const marketingKeys = {
  subscribers: (page: number) => ['marketing', 'subscribers', page] as const,
  messages: (page: number) => ['marketing', 'messages', page] as const,
};

// Subscribers
export function useSubscribers(page: number = 1) {
  return useQuery<PaginatedResponse<Subscriber>>({
    queryKey: marketingKeys.subscribers(page),
    queryFn: () => marketingApi.getSubscribers(page),
    placeholderData: keepPreviousData,
  });
}

export function useDeleteSubscriber() {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: marketingApi.deleteSubscriber,
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['marketing', 'subscribers'] }),
  });
}

export function useSendNewsletter() {
  return useMutation({
    mutationFn: (payload: SendNewsletterPayload) => marketingApi.sendNewsletter(payload),
  });
}

// Contact Messages
export function useContactMessages(page: number = 1) {
  return useQuery<PaginatedResponse<ContactMessage>>({
    queryKey: marketingKeys.messages(page),
    queryFn: () => marketingApi.getContactMessages(page),
    placeholderData: keepPreviousData,
  });
}

export function useDeleteContactMessage() {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: marketingApi.deleteContactMessage,
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['marketing', 'messages'] }),
  });
}