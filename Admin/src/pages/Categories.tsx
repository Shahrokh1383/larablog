import { useState } from 'react';
import {
  useCategories,
  useCategoryManager,
  CategoryDataTable,
} from '@/features/categories';
import EntityFormModal from '@/shared/components/EntityFormModal';

export default function CategoriesPage() {
  const [page, setPage] = useState(1);
  const { data: paginatedResponse, isLoading, isError } = useCategories({ page });
  
  const {
    showModal,
    editingCategory,
    serverError,
    isLoading: isMutating,
    openCreate,
    openEdit,
    handleDelete,
    handleSubmit,
    closeModal,
  } = useCategoryManager();

  const categories = paginatedResponse?.data ?? [];
  const meta = paginatedResponse?.meta;

  return (
    <div className="container py-4">
      <div className="d-flex justify-content-between align-items-center mb-4">
        <h1>Categories</h1>
        <button className="btn btn-primary" onClick={openCreate}>
          <i className="fas fa-plus me-2"></i>New Category
        </button>
      </div>

      <div className="card shadow-sm">
        <div className="card-body">
          <CategoryDataTable
            categories={categories}
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
        title={editingCategory ? 'Edit Category' : 'Create Category'}
        initialName={editingCategory?.name ?? ''}
        onSubmit={handleSubmit}
        isLoading={isMutating}
        serverError={serverError}
        onClose={closeModal}
        entityIcon="folder"
      />
    </div>
  );
}