export { postsApi } from './api/postsApi';
export { usePosts } from './hooks/usePosts';
export { usePostMutations } from './hooks/usePostMutations';
export { default as PostDataTable } from './components/PostDataTable';
export { default as PostForm } from './components/PostForm';
export { default as RichTextEditor } from './components/RichTextEditor';
export { default as MultiSelectTags } from './components/MultiSelectTags';
export type { Post, PostFormData, PostTag } from './types/post';

export const postKeys = {
  all: ['posts'] as const,
  lists: () => [...postKeys.all, 'list'] as const,
  list: (filters: { page?: number; search?: string }) =>
    [...postKeys.lists(), filters] as const,
  details: () => [...postKeys.all, 'detail'] as const,
  detail: (id: string) => [...postKeys.details(), id] as const,
};