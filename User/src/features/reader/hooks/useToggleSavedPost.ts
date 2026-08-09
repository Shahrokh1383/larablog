import { useMutation, useQueryClient } from '@tanstack/react-query';
import { readerApi } from '../api/readerApi';
import { postKeys } from '@/features/posts/hooks/usePost';
import { readerKeys } from './useSavedPosts';
import { dashboardKeys } from '@/features/dashboard/hooks/useDashboardOverview';
import type { Post } from '@/features/posts/types/post';

export function useToggleSavedPost(postId: string, slug: string) {
  const queryClient = useQueryClient();
  const postQueryKey = postKeys.detail(slug);

  return useMutation({
    mutationFn: () => readerApi.toggleSave(postId),
    onMutate: async () => {
      await queryClient.cancelQueries({ queryKey: postQueryKey });
      
      const previousPost = queryClient.getQueryData<Post>(postQueryKey);
      
      queryClient.setQueryData<Post>(postQueryKey, (old) => {
        if (!old) return old;
        return {
          ...old,
          is_saved: !old.is_saved,
        };
      });

      return { previousPost };
    },
    onError: (err, variables, context) => {
      if (context?.previousPost) {
        queryClient.setQueryData(postQueryKey, context.previousPost);
      }
    },
    onSuccess: (data) => {
      // Only update if we have valid data from the server
      if (data && typeof data.saved === 'boolean') {
        queryClient.setQueryData<Post>(postQueryKey, (old) => {
          if (!old) return old;
          return {
            ...old,
            is_saved: data.saved,
          };
        });
      }
      
      queryClient.invalidateQueries({ queryKey: readerKeys.savedPosts() });
      queryClient.invalidateQueries({ queryKey: dashboardKeys.overview() });
    },
  });
}