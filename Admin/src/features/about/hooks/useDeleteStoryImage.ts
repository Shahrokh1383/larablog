import { useMutation, useQueryClient } from '@tanstack/react-query';
import { aboutApi } from '../api/aboutApi';

export function useDeleteStoryImage() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (url: string) => aboutApi.deleteStoryImage(url),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['about', 'settings'] });
    },
  });
}