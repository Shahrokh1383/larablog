import { useMutation, useQueryClient } from '@tanstack/react-query';
import { tagsApi } from '../api/tagsApi';
import { tagKeys } from '../index';
import type { TagFormData } from '../types/tag';

export function useTagMutations() {
  const queryClient = useQueryClient();

  const createTag = useMutation({
    mutationFn: (data: TagFormData) => tagsApi.create(data),
    onSuccess: () => queryClient.invalidateQueries({ queryKey: tagKeys.all }),
  });

  const updateTag = useMutation({
    mutationFn: ({ id, data }: { id: string; data: TagFormData }) =>
      tagsApi.update(id, data),
    onSuccess: () => queryClient.invalidateQueries({ queryKey: tagKeys.all }),
  });

  const deleteTag = useMutation({
    mutationFn: (id: string) => tagsApi.delete(id),
    onSuccess: () => queryClient.invalidateQueries({ queryKey: tagKeys.all }),
  });

  return { createTag, updateTag, deleteTag };
}