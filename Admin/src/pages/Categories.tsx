import { useState } from 'react';
import { useCategories, useCategoryMutations, CategoryDataTable } from '@/features/categories';
import { useAdminAuth } from '@/features/auth/hooks/useAdminAuth';
import type { Category } from '@/features/categories';
import { AxiosError } from 'axios';
import EntityFormModal from '@/shared/components/EntityFormModal';

export default function CategoriesPage() {
  const { user } = useAdminAuth();
  const [page, setPage] = useState(1);

  const [showModal, setShowModal] = useState(false);
  const [editingCategory, setEditingCategory] = useState<Category | null>(null);
  const [serverError, setServerError] = useState<string | null>(null);

  // FIX: Ensure canMutate is strictly boolean
  const roles = user?.roles ?? [];
  const canMutate = roles.includes('admin') || roles.includes('editor');

  const { data: paginatedResponse, isLoading, isError } = useCategories({ page });
  const { createCategory, updateCategory, deleteCategory } = useCategoryMutations();

  const categories = paginatedResponse?.data ?? [];
  const meta = paginatedResponse?.meta;

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

  const closeModal = () => {
    setShowModal(false);
    setEditingCategory(null);
    setServerError(null);
  };

  const handleDelete = (category: Category) => {
    if (window.confirm(`Delete category "${category.name}"? This action cannot be undone.`)) {
      deleteCategory.mutate(category.id);
    }
  };

  const handleSubmit = (data: { name: string }) => {
    setServerError(null);
    const payload = { name: data.name.trim() };

    if (editingCategory) {
      updateCategory.mutate(
        { id: editingCategory.id, data: payload },
        {
          onSuccess: closeModal,
          onError: (error) => {
            const axiosErr = error as AxiosError<{ message?: string }>;
            setServerError(axiosErr.response?.data?.message ?? 'Update failed');
          },
        }
      );
    } else {
      createCategory.mutate(payload, {
        onSuccess: closeModal,
        onError: (error) => {
          const axiosErr = error as AxiosError<{ message?: string }>;
          setServerError(axiosErr.response?.data?.message ?? 'Create failed');
        },
      });
    }
  };

  return (
    <div className="container py-4">
      <div className="d-flex justify-content-between align-items-center mb-4">
        <h1>Categories</h1>
        {canMutate && (
          <button className="btn btn-primary" onClick={openCreate}>
            <i className="fas fa-plus me-2"></i>New Category
          </button>
        )}
      </div>

      <div className="card shadow-sm">
        <div className="card-body">
          <CategoryDataTable
            categories={categories}
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
        title={editingCategory ? 'Edit Category' : 'Create Category'}
        initialName={editingCategory?.name ?? ''}
        onSubmit={handleSubmit}
        isLoading={createCategory.isPending || updateCategory.isPending}
        serverError={serverError}
        onClose={closeModal}
        entityIcon="folder"
      />
    </div>
  );
}