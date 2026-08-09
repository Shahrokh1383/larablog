import { useMutation, useQueryClient } from '@tanstack/react-query';
import { profileApi } from '../api/profileApi';
import { profileKeys } from './useProfile';

export function useDeleteAvatar() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (url: string) => profileApi.deleteAvatar(url),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: profileKeys.detail() });
      queryClient.invalidateQueries({ queryKey: ['auth', 'user'] });
    },
  });
}