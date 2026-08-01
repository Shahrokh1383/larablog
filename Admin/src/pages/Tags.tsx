import { useState } from 'react';
import { useTags, useTagMutations, TagDataTable, TagFormModal } from '@/features/tags';
import type { Tag, TagFormData } from '@/features/tags';
import { AxiosError } from 'axios';

export default function TagsPage() {
  const { data: tags = [], isLoading, isError } = useTags();
  const { createTag, updateTag, deleteTag } = useTagMutations();

  const [showModal, setShowModal] = useState(false);
  const [editingTag, setEditingTag] = useState<Tag | null>(null);
  const [serverError, setServerError] = useState<string | null>(null);

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

  const handleSubmit = (data: TagFormData) => {
    setServerError(null);
    if (editingTag) {
      updateTag.mutate(
        { id: editingTag.id, data },
        {
          onSuccess: () => setShowModal(false),
          onError: (err) => {
            const axiosErr = err as AxiosError<{ message: string }>;
            setServerError(axiosErr.response?.data?.message ?? 'Update failed');
          },
        }
      );
    } else {
      createTag.mutate(data, {
        onSuccess: () => setShowModal(false),
        onError: (err) => {
          const axiosErr = err as AxiosError<{ message: string }>;
          setServerError(axiosErr.response?.data?.message ?? 'Create failed');
        },
      });
    }
  };

  return (
    <div className="container py-4">
      <div className="d-flex justify-content-between align-items-center mb-4">
        <h1>Tags</h1>
        <button className="btn btn-primary" onClick={openCreate}>
          <i className="fas fa-plus me-2"></i>New Tag
        </button>
      </div>

      <div className="card shadow-sm">
        <div className="card-body">
          <TagDataTable
            tags={tags}
            isLoading={isLoading}
            isError={isError}
            onEdit={openEdit}
            onDelete={handleDelete}
          />
        </div>
      </div>

      <TagFormModal
        show={showModal}
        title={editingTag ? 'Edit Tag' : 'Create Tag'}
        initialName={editingTag?.name ?? ''}
        onSubmit={handleSubmit}
        isLoading={createTag.isPending || updateTag.isPending}
        serverError={serverError}
        onClose={() => setShowModal(false)}
      />
    </div>
  );
}