import { useMutation, useQueryClient } from '@tanstack/react-query';
import { readerApi } from '../api/readerApi';
import { postKeys } from '@/features/posts/hooks/usePost';
import { dashboardKeys } from '@/features/dashboard/hooks/useDashboardOverview';

export function useToggleSavedPost(postId: string) {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: () => readerApi.toggleSave(postId),
    onMutate: async () => {
      await queryClient.cancelQueries({ queryKey: postKeys.detail(postId) });
      const previousPost = queryClient.getQueryData<any>(postKeys.detail(postId));
      
      queryClient.setQueryData(postKeys.detail(postId), (old: any) => ({
        ...old,
        is_saved: !old?.is_saved,
      }));

      return { previousPost };
    },
    onError: (err, variables, context) => {
      if (context?.previousPost) {
        queryClient.setQueryData(postKeys.detail(postId), context.previousPost);
      }
    },
    onSuccess: (data) => {
      queryClient.setQueryData(postKeys.detail(postId), (old: any) => ({
        ...old,
        is_saved: data.saved,
      }));
      // Invalidate saved posts list
      queryClient.invalidateQueries({ queryKey: ['reader', 'saved-posts'] });
      // Also invalidate dashboard overview so header counts update
      queryClient.invalidateQueries({ queryKey: dashboardKeys.overview() });
    },
  });
}