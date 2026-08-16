import { useState } from 'react';
import { useTagMutations } from './useTagMutations';
import type { Tag, TagFormData } from '../types/tag';
import { AxiosError } from 'axios';

export function useTagManager() {
  const [showModal, setShowModal] = useState(false);
  const [editingTag, setEditingTag] = useState<Tag | null>(null);
  const [serverError, setServerError] = useState<string | null>(null);

  const { createTag, updateTag, deleteTag } = useTagMutations();

  const openCreate = () => {
    setEditingTag(null);
    setServerError(null);
    setShowModal(true);
  };

  const openEdit = (tag: Tag) => {
    setEditingTag(tag);
    setServerError(null);
    setShowModal(true);
  };

  const handleDelete = (tag: Tag) => {
    if (window.confirm(`Delete tag "${tag.name}"?`)) {
      deleteTag.mutate(tag.id);
    }
  };

  const handleSubmit = (formData: TagFormData) => {
    setServerError(null);
    
    if (editingTag) {
      updateTag.mutate(
        { id: editingTag.id, data: formData },
        {
          onSuccess: () => setShowModal(false),
          onError: (err) => {
            const axiosErr = err as AxiosError<{ message: string }>;
            setServerError(axiosErr.response?.data?.message ?? 'Update failed');
          },
        }
      );
    } else {
      createTag.mutate(formData, {
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
    editingTag,
    serverError,
    isLoading: createTag.isPending || updateTag.isPending,
    openCreate,
    openEdit,
    handleDelete,
    handleSubmit,
    closeModal: () => setShowModal(false),
  };
}