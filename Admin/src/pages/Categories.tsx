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
  const { data: categories = [], isLoading, isError } = useCategories();
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

  const handleSubmit = (data: CategoryFormData) => {
    setServerError(null);
    if (editingCategory) {
      updateCategory.mutate(
        { id: editingCategory.id, data },
        {
          onSuccess: () => setShowModal(false),
          onError: (err) => {
            const axiosErr = err as AxiosError<{ message: string }>;
            setServerError(axiosErr.response?.data?.message ?? 'Update failed');
          },
        }
      );
    } else {
      createCategory.mutate(data, {
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
          <CategoryDataTable
            categories={categories}
            isLoading={isLoading}
            isError={isError}
            onEdit={openEdit}
            onDelete={handleDelete}
          />
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