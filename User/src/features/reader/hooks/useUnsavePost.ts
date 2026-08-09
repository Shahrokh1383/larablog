import { useMutation, useQueryClient } from '@tanstack/react-query';
import { readerApi } from '../api/readerApi';
import { readerKeys } from './useSavedPosts';
import { dashboardKeys } from '@/features/dashboard/hooks/useDashboardOverview';

export function useUnsavePost() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (postId: string) => readerApi.toggleSave(postId),
    onSuccess: () => {
      // Invalidate saved posts list and dashboard overview counts
      queryClient.invalidateQueries({ queryKey: readerKeys.savedPosts() });
      queryClient.invalidateQueries({ queryKey: dashboardKeys.overview() });
    },
  });
}