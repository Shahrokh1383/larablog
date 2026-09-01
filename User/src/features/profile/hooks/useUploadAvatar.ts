import { useMutation, useQueryClient } from '@tanstack/react-query';
import { profileApi } from '../api/profileApi';
import { profileKeys } from './useProfile';

export function useUploadAvatar() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (file: File) => profileApi.uploadAvatar(file),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: profileKeys.detail() });
      queryClient.invalidateQueries({ queryKey: ['auth', 'user'] });
    },
  });
}