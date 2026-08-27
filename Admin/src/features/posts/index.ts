export { postsApi } from './api/postsApi';
export { usePosts } from './hooks/usePosts';
export { usePostDetail } from './hooks/usePostDetail';
export { usePostMutations, parseAxiosError } from './hooks/usePostMutations';
export { usePostForm } from './hooks/usePostForm';
export { default as PostDataTable } from './components/PostDataTable';
export { default as PostForm } from './components/PostForm';
export { default as RichTextEditor } from './components/RichTextEditor';
export { default as MultiSelectTags } from './components/MultiSelectTags';
export type { Post, PostFormData, PostTag, PostUser, PostCategory } from './types/post';

export const postKeys = {
  all: ['posts'] as const,
  lists: () => [...postKeys.all, 'list'] as const,
  list: (filters: { page?: number; search?: string; isEditorsPick?: boolean }) =>
    [...postKeys.lists(), filters] as const,
  details: () => [...postKeys.all, 'detail'] as const,
  detail: (id: string) => [...postKeys.details(), id] as const,
};