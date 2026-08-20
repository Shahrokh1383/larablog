export { useCategories } from './hooks/useCategories';
export { useCategoryMutations } from './hooks/useCategoryMutations';
export { default as CategoryDataTable } from './components/CategoryDataTable';
export type { Category, CategoryFormData } from './types/category';

export const categoryKeys = {
  all: ['categories'] as const,
  lists: () => [...categoryKeys.all, 'list'] as const,
  list: (filters: { page?: number; perPage?: number }) =>
    [...categoryKeys.lists(), filters] as const,
};