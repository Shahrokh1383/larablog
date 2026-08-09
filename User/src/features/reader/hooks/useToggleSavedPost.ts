import { useMutation, useQueryClient } from '@tanstack/react-query';
import { readerApi } from '../api/readerApi';
import { postKeys } from '@/features/posts/hooks/usePost';

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
      queryClient.invalidateQueries({ queryKey: ['reader', 'saved-posts'] });
    },
  });
}