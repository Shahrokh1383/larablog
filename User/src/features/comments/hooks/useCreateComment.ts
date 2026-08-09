import { useMutation, useQueryClient } from '@tanstack/react-query';
import { commentsApi } from '../api/commentsApi';
import { commentKeys } from './useComments';
import { postKeys } from '@/features/posts/hooks/usePost';
import { dashboardKeys } from '@/features/dashboard/hooks/useDashboardOverview';
import type { CreateCommentPayload } from '../types/comment';

export function useCreateComment() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (data: CreateCommentPayload) => commentsApi.create(data),
    onSuccess: () => {
      // 1. Update standard comment lists and post details
      queryClient.invalidateQueries({ queryKey: commentKeys.lists() });
      queryClient.invalidateQueries({ queryKey: postKeys.details() });

      // 2. CROSS-FEATURE INVALIDATION: Instantly update Dashboard counts and lists
      queryClient.invalidateQueries({ queryKey: dashboardKeys.overview() });
      queryClient.invalidateQueries({ queryKey: dashboardKeys.comments() });
    },
  });
}