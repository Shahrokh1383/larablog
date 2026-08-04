import { useMutation, useQueryClient } from '@tanstack/react-query';
import { profileApi } from '../api/profileApi';
import { UpdateProfilePayload } from '../types/profile';
import { profileKeys } from './useProfile';

export function useUpdateProfile() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (payload: UpdateProfilePayload) => profileApi.update(payload),
    onSuccess: (updatedProfile) => {
      // 1. Update the profile cache immediately
      queryClient.setQueryData(profileKeys.detail(), updatedProfile);
      
      // 2. Synchronize the global Auth state so the AdminLayout updates instantly
      queryClient.invalidateQueries({ queryKey: ['auth', 'user'] });
    },
    onError: (error: any) => {
      // Handle backend validation errors (Article VIII compliance)
      const message = error?.response?.data?.message || 'Failed to update profile.';
    },
  });
}