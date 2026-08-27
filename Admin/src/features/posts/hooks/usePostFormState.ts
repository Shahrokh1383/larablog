import { useState } from 'react';
import type { PostFormData } from '../types/post';

export function usePostFormState(initialData?: Partial<PostFormData>) {
  const [title, setTitle] = useState(initialData?.title ?? '');
  const [body, setBody] = useState(initialData?.body ?? '');
  const [excerpt, setExcerpt] = useState(initialData?.excerpt ?? '');
  const [featuredImage, setFeaturedImage] = useState(initialData?.featured_image ?? '');
  const [isPublished, setIsPublished] = useState(initialData?.is_published ?? false);
  const [isEditorsPick, setIsEditorsPick] = useState(initialData?.is_editors_pick ?? false);
  const [categoryId, setCategoryId] = useState(initialData?.category_id ?? '');
  const [tagIds, setTagIds] = useState<string[]>(initialData?.tag_ids ?? []);

  return {
    title,
    setTitle,
    body,
    setBody,
    excerpt,
    setExcerpt,
    featuredImage,
    setFeaturedImage,
    isPublished,
    setIsPublished,
    isEditorsPick,
    setIsEditorsPick,
    categoryId,
    setCategoryId,
    tagIds,
    setTagIds,
  };
}