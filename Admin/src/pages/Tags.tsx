import { useState } from 'react';
import { useTags, useTagMutations, TagDataTable } from '@/features/tags';
import { useAdminAuth } from '@/features/auth/hooks/useAdminAuth';
import type { Tag } from '@/features/tags';
import { AxiosError } from 'axios';
import EntityFormModal from '@/shared/components/EntityFormModal';

export default function TagsPage() {
  const { user } = useAdminAuth();
  const [page, setPage] = useState(1);

  // Modal state (orchestrated in page, matching Posts.tsx pattern)
  const [showModal, setShowModal] = useState(false);
  const [editingTag, setEditingTag] = useState<Tag | null>(null);
  const [formName, setFormName] = useState('');
  const [validationErrors, setValidationErrors] = useState<{ [key: string]: string[] } | null>(null);

  const canMutate = user?.roles.includes('admin') || user?.roles.includes('editor');

  const { data: paginatedResponse, isLoading, isError } = useTags({ page });
  const { createTag, updateTag, deleteTag } = useTagMutations();

  const tags = paginatedResponse?.data ?? [];
  const meta = paginatedResponse?.meta;

  const openCreate = () => {
    setEditingTag(null);
    setFormName('');
    setValidationErrors(null);
    setShowModal(true);
  };

  const openEdit = (tag: Tag) => {
    setEditingTag(tag);
    setFormName(tag.name);
    setValidationErrors(null);
    setShowModal(true);
  };

  const closeModal = () => {
    setShowModal(false);
    setEditingTag(null);
    setFormName('');
    setValidationErrors(null);
  };

  const handleDelete = (tag: Tag) => {
    if (window.confirm(`Delete tag "${tag.name}"? This action cannot be undone.`)) {
      deleteTag.mutate(tag.id);
    }
  };

  const handleSubmit = () => {
    setValidationErrors(null);
    const payload = { name: formName.trim() };

    const mutation = editingTag ? updateTag : createTag;
    const args = editingTag ? { id: editingTag.id, data: payload } : payload;

    mutation.mutate(args as never, {
      onSuccess: () => {
        closeModal();
      },
      onError: (error) => {
        const axiosErr = error as AxiosError<{ errors?: { [key: string]: string[] }; message?: string }>;
        if (axiosErr.response?.data?.errors) {
          setValidationErrors(axiosErr.response.data.errors);
        }
      },
    });
  };

  return (
    <div className="container py-4">
      <div className="d-flex justify-content-between align-items-center mb-4">
        <h1>Tags</h1>
        {canMutate && (
          <button className="btn btn-primary" onClick={openCreate}>
            <i className="fas fa-plus me-2"></i>New Tag
          </button>
        )}
      </div>

      <div className="card shadow-sm">
        <div className="card-body">
          <TagDataTable
            tags={tags}
            isLoading={isLoading}
            isError={isError}
            canEdit={canMutate}
            canDelete={canMutate}
            onEdit={openEdit}
            onDelete={handleDelete}
          />

          {meta && meta.last_page > 1 && (
            <div className="d-flex justify-content-center mt-4">
              <nav>
                <ul className="pagination mb-0">
                  <li className={`page-item ${page <= 1 ? 'disabled' : ''}`}>
                    <button
                      className="page-link"
                      onClick={() => setPage((p) => Math.max(p - 1, 1))}
                    >
                      Previous
                    </button>
                  </li>
                  <li className="page-item active">
                    <span className="page-link">
                      Page {meta.current_page} of {meta.last_page}
                    </span>
                  </li>
                  <li className={`page-item ${page >= meta.last_page ? 'disabled' : ''}`}>
                    <button
                      className="page-link"
                      onClick={() => setPage((p) => p + 1)}
                    >
                      Next
                    </button>
                  </li>
                </ul>
              </nav>
            </div>
          )}
        </div>
      </div>

      <EntityFormModal
        show={showModal}
        title={editingTag ? 'Edit Tag' : 'Create Tag'}
        name={formName}
        onNameChange={setFormName}
        onSubmit={handleSubmit}
        isLoading={createTag.isPending || updateTag.isPending}
        validationErrors={validationErrors}
        onClose={closeModal}
        entityIcon="tags"
      />
    </div>
  );
}