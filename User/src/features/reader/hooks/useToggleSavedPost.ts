import { useMutation, useQueryClient } from '@tanstack/react-query';
import { readerApi } from '../api/readerApi';
import { postKeys } from '@/features/posts/hooks/usePost';

export function useToggleSavedPost(postId: string, initialSavedState: boolean) {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: () => readerApi.toggleSave(postId),
    onMutate: async () => {
      // Optimistic update
      await queryClient.cancelQueries({ queryKey: postKeys.detail(postId) });
      const previousPost = queryClient.getQueryData<any>(postKeys.detail(postId));
      
      queryClient.setQueryData(postKeys.detail(postId), (old: any) => ({
        ...old,
        is_saved: !old?.is_saved,
      }));

      return { previousPost };
    },
    onError: (err, variables, context) => {
      // Rollback on error
      if (context?.previousPost) {
        queryClient.setQueryData(postKeys.detail(postId), context.previousPost);
      }
    },
    onSuccess: (data) => {
      // Ensure state matches backend response
      queryClient.setQueryData(postKeys.detail(postId), (old: any) => ({
        ...old,
        is_saved: data.saved,
      }));
      // Invalidate dashboard saved posts list
      queryClient.invalidateQueries({ queryKey: ['reader', 'saved-posts'] });
    },
  });
}