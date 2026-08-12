import { useMutation } from '@tanstack/react-query';
import { aboutApi } from '../api/aboutApi';

export function useUploadStoryImage() {
  return useMutation({
    mutationFn: (file: File) => aboutApi.uploadStoryImage(file),
  });
}