import { useMutation, useQueryClient } from '@tanstack/react-query';
import { categoriesApi } from '../api/categoriesApi';
import { categoryKeys } from '../index';
import type { CategoryFormData } from '../types/category';
import { AxiosError } from 'axios';

export function useCategoryMutations() {
  const queryClient = useQueryClient();

  const createCategory = useMutation({
    mutationFn: (data: CategoryFormData) => categoriesApi.create(data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: categoryKeys.all });
      alert('Category created successfully!');
    },
  });

  const updateCategory = useMutation({
    mutationFn: ({ id, data }: { id: string; data: CategoryFormData }) =>
      categoriesApi.update(id, data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: categoryKeys.all });
      alert('Category updated successfully!');
    },
  });

  const deleteCategory = useMutation({
    mutationFn: (id: string) => categoriesApi.delete(id),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: categoryKeys.all });
      alert('Category deleted successfully!');
    },
    onError: (error: AxiosError<{ message?: string }>) => {
      const errorMessage = error.response?.data?.message ?? 'Failed to delete category';
      alert(errorMessage);
    },
  });

  return { createCategory, updateCategory, deleteCategory };
}