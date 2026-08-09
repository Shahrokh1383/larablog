import { useMutation, useQueryClient } from '@tanstack/react-query';
import { profileApi, UpdateProfilePayload } from '../api/profileApi';
import { profileKeys } from './useProfile';

export function useUpdateProfile() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (data: UpdateProfilePayload) => profileApi.updateProfile(data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: profileKeys.detail() });
      queryClient.invalidateQueries({ queryKey: ['auth', 'user'] });
    },
  });
}