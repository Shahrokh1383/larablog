export { postsApi } from './api/postsApi';
export { usePosts } from './hooks/usePosts';
export { usePostMutations } from './hooks/usePostMutations';
export { default as PostDataTable } from './components/PostDataTable';
export { default as PostForm } from './components/PostForm';
export { default as PostFormModal } from './components/PostFormModal';
export type { Post, PostFormData, PostTag } from './types/post';

export const postKeys = {
  all: ['posts'] as const,
  lists: () => [...postKeys.all, 'list'] as const,
  details: () => [...postKeys.all, 'detail'] as const,
  detail: (id: string) => [...postKeys.details(), id] as const,
};