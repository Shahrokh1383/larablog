export { useTags } from './hooks/useTags';
export { useTagMutations } from './hooks/useTagMutations';
export { default as TagDataTable } from './components/TagDataTable';
export { default as TagFormModal } from './components/TagFormModal';
export type { Tag, TagFormData } from './types/tag';

export const tagKeys = {
  all: ['tags'] as const,
  lists: () => [...tagKeys.all, 'list'] as const,
};