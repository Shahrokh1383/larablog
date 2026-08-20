import { useMutation, useQueryClient } from '@tanstack/react-query';
import { tagsApi } from '../api/tagsApi';
import { tagKeys } from '../index';
import type { TagFormData } from '../types/tag';
import { AxiosError } from 'axios';

export interface ValidationErrors {
  [key: string]: string[];
}

export function useTagMutations() {
  const queryClient = useQueryClient();

  const createTag = useMutation({
    mutationFn: (data: TagFormData) => tagsApi.create(data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: tagKeys.all });
      alert('Tag created successfully!'); // TODO: Replace with toast provider
    },
    onError: (error: AxiosError<{ errors?: ValidationErrors; message?: string }>) => {
      const errorMessage = error.response?.data?.message ?? 'Failed to create tag';
      alert(errorMessage);
    },
  });

  const updateTag = useMutation({
    mutationFn: ({ id, data }: { id: string; data: TagFormData }) =>
      tagsApi.update(id, data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: tagKeys.all });
      alert('Tag updated successfully!'); // TODO: Replace with toast provider
    },
    onError: (error: AxiosError<{ errors?: ValidationErrors; message?: string }>) => {
      const errorMessage = error.response?.data?.message ?? 'Failed to update tag';
      alert(errorMessage);
    },
  });

  const deleteTag = useMutation({
    mutationFn: (id: string) => tagsApi.delete(id),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: tagKeys.all });
      alert('Tag deleted successfully!');
    },
    onError: (error: AxiosError<{ message?: string }>) => {
      const errorMessage = error.response?.data?.message ?? 'Failed to delete tag';
      alert(errorMessage);
    },
  });

  return { createTag, updateTag, deleteTag };
}