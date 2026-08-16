export { useTags } from './hooks/useTags';
export { useTagMutations } from './hooks/useTagMutations';
export { useTagManager } from './hooks/useTagManager';
export { default as TagDataTable } from './components/TagDataTable';
export type { Tag, TagFormData } from './types/tag';

export const tagKeys = {
  all: ['tags'] as const,
  lists: () => [...tagKeys.all, 'list'] as const,
  list: (filters: { page?: number; perPage?: number }) =>
    [...tagKeys.lists(), filters] as const,
};