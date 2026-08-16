import { useState } from 'react';
import { useCategoryMutations } from './useCategoryMutations';
import type { Category, CategoryFormData } from '../types/category';
import { AxiosError } from 'axios';

export function useCategoryManager() {
  const [showModal, setShowModal] = useState(false);
  const [editingCategory, setEditingCategory] = useState<Category | null>(null);
  const [serverError, setServerError] = useState<string | null>(null);

  const { createCategory, updateCategory, deleteCategory } = useCategoryMutations();

  const openCreate = () => {
    setEditingCategory(null);
    setServerError(null);
    setShowModal(true);
  };

  const openEdit = (category: Category) => {
    setEditingCategory(category);
    setServerError(null);
    setShowModal(true);
  };

  const handleDelete = (category: Category) => {
    if (window.confirm(`Delete category "${category.name}"?`)) {
      deleteCategory.mutate(category.id);
    }
  };

  const handleSubmit = (formData: CategoryFormData) => {
    setServerError(null);
    
    if (editingCategory) {
      updateCategory.mutate(
        { id: editingCategory.id, data: formData },
        {
          onSuccess: () => setShowModal(false),
          onError: (err) => {
            const axiosErr = err as AxiosError<{ message: string }>;
            setServerError(axiosErr.response?.data?.message ?? 'Update failed');
          },
        }
      );
    } else {
      createCategory.mutate(formData, {
        onSuccess: () => setShowModal(false),
        onError: (err) => {
          const axiosErr = err as AxiosError<{ message: string }>;
          setServerError(axiosErr.response?.data?.message ?? 'Create failed');
        },
      });
    }
  };

  return {
    showModal,
    editingCategory,
    serverError,
    isLoading: createCategory.isPending || updateCategory.isPending,
    openCreate,
    openEdit,
    handleDelete,
    handleSubmit,
    closeModal: () => setShowModal(false),
  };
}