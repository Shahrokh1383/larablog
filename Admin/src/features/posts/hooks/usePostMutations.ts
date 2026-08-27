import { useMutation, useQueryClient } from '@tanstack/react-query';
import { postsApi } from '../api/postsApi';
import { postKeys } from '../index';
import type { PostFormData } from '../types/post';

export interface ParsedApiError {
  serverError: string;
  fieldErrors: Record<string, string>;
}

export function parseAxiosError(err: unknown): ParsedApiError {
  const axiosErr = err as {
    response?: {
      data?: {
        message?: string;
        errors?: Record<string, string[]>;
      };
    };
    message?: string;
  };

  const data = axiosErr.response?.data;
  const fieldErrors: Record<string, string> = {};

  if (data?.errors && typeof data.errors === 'object') {
    Object.entries(data.errors).forEach(([key, messages]) => {
      if (Array.isArray(messages) && messages.length > 0) {
        fieldErrors[key] = messages[0];
      }
    });
  }

  return {
    serverError:
      data?.message ??
      axiosErr.message ??
      'An unexpected error occurred. Please try again.',
    fieldErrors,
  };
}

export function usePostMutations() {
  const queryClient = useQueryClient();

  const invalidateLists = () => {
    queryClient.invalidateQueries({ queryKey: postKeys.lists() });
  };

  const invalidateDetail = (id: string) => {
    queryClient.invalidateQueries({ queryKey: postKeys.detail(id) });
  };

  const createPost = useMutation({
    mutationFn: (data: PostFormData) => postsApi.create(data),
    onSuccess: invalidateLists,
  });

  const updatePost = useMutation({
    mutationFn: ({ id, data }: { id: string; data: Partial<PostFormData> }) =>
      postsApi.update(id, data),
    onSuccess: (_, variables) => {
      invalidateLists();
      invalidateDetail(variables.id);
    },
  });

  const deletePost = useMutation({
    mutationFn: (id: string) => postsApi.delete(id),
    onSuccess: invalidateLists,
  });

  const uploadImage = useMutation({
    mutationFn: (file: File) => postsApi.uploadImage(file),
  });

  const deleteImage = useMutation({
    mutationFn: (url: string) => postsApi.deleteImage(url),
  });

  return {
    createPost,
    updatePost,
    deletePost,
    uploadImage,
    deleteImage,
  };
}