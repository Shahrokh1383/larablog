import { useState } from 'react';
import {
  useTags,
  useTagManager,
  TagDataTable,
} from '@/features/tags';
import EntityFormModal from '@/shared/components/EntityFormModal';

export default function TagsPage() {
  const [page, setPage] = useState(1);
  const { data: paginatedResponse, isLoading, isError } = useTags({ page });
  
  const {
    showModal,
    editingTag,
    serverError,
    isLoading: isMutating,
    openCreate,
    openEdit,
    handleDelete,
    handleSubmit,
    closeModal,
  } = useTagManager();

  const tags = paginatedResponse?.data ?? [];
  const meta = paginatedResponse?.meta;

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

          {meta && (
            <div className="d-flex justify-content-center mt-4">
              <nav>
                <ul className="pagination">
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
                  <li className={`page-item ${page >= (meta.last_page ?? 1) ? 'disabled' : ''}`}>
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
        initialName={editingTag?.name ?? ''}
        onSubmit={handleSubmit}
        isLoading={isMutating}
        serverError={serverError}
        onClose={closeModal}
        entityIcon="tags"
      />
    </div>
  );
}