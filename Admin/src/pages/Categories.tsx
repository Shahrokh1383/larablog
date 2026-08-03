import { useState } from 'react';
import {
  useCategories,
  useCategoryMutations,
  CategoryDataTable,
  CategoryFormModal,
} from '@/features/categories';
import type { Category, CategoryFormData } from '@/features/categories';
import { AxiosError } from 'axios';

export default function CategoriesPage() {
  const [page, setPage] = useState(1);
  const { data, isLoading, isError } = useCategories(page);
  const { createCategory, updateCategory, deleteCategory } = useCategoryMutations();

  const [showModal, setShowModal] = useState(false);
  const [editingCategory, setEditingCategory] = useState<Category | null>(null);
  const [serverError, setServerError] = useState<string | null>(null);

  const openCreate = () => {
    setEditingCategory(null);
    setServerError(null);
    setShowModal(true);
  };

  const openEdit = (cat: Category) => {
    setEditingCategory(cat);
    setServerError(null);
    setShowModal(true);
  };

  const handleDelete = (cat: Category) => {
    if (window.confirm(`Delete category "${cat.name}"?`)) {
      deleteCategory.mutate(cat.id);
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
          {isLoading && (
            <div className="text-center py-5">
              <div className="spinner-border" />
            </div>
          )}
          {isError && <div className="alert alert-danger m-4">Failed to load categories.</div>}

          {!isLoading && !isError && data?.data && (
            <CategoryDataTable
              categories={data.data}
              isLoading={false}
              isError={false}
              onEdit={openEdit}
              onDelete={handleDelete}
            />
          )}

          {data?.meta && (
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
                      Page {data.meta.current_page} of {data.meta.last_page}
                    </span>
                  </li>
                  <li className={`page-item ${page >= (data.meta.last_page ?? 1) ? 'disabled' : ''}`}>
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

      <CategoryFormModal
        show={showModal}
        title={editingCategory ? 'Edit Category' : 'Create Category'}
        initialName={editingCategory?.name ?? ''}
        onSubmit={handleSubmit}
        isLoading={createCategory.isPending || updateCategory.isPending}
        serverError={serverError}
        onClose={() => setShowModal(false)}
      />
    </div>
  );
}