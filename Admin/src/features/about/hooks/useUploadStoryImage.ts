import { useMutation, useQueryClient } from '@tanstack/react-query';
import { aboutApi } from '../api/aboutApi';

export function useUploadStoryImage() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (file: File) => aboutApi.uploadStoryImage(file),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['about', 'settings'] });
    },
  });
}