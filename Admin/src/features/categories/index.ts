export { useCategories } from './hooks/useCategories';
export { useCategoryMutations } from './hooks/useCategoryMutations';
export { default as CategoryDataTable } from './components/CategoryDataTable';
export { default as CategoryFormModal } from './components/CategoryFormModal';
export type { Category, CategoryFormData } from './types/category';

export const categoryKeys = {
  all: ['categories'] as const,
  lists: () => [...categoryKeys.all, 'list'] as const,
  list: (filters: { page?: number }) =>
    [...categoryKeys.lists(), filters] as const,
};