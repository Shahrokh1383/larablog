import { useMutation } from '@tanstack/react-query';
import { profileApi } from '../api/profileApi';

export function useUploadAvatar() {
  return useMutation({
    mutationFn: (file: File) => profileApi.uploadAvatar(file),
  });
}