import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { usePostMutations, parseAxiosError } from './usePostMutations';
import type { PostFormData } from '../types/post';

interface UsePostFormOptions {
  postId?: string;
  onSuccess?: () => void;
}

export function usePostForm({ postId, onSuccess }: UsePostFormOptions) {
  const navigate = useNavigate();
  const isEditing = !!postId;

  const { createPost, updatePost, uploadImage, deleteImage } = usePostMutations();

  const [fieldErrors, setFieldErrors] = useState<Record<string, string>>({});
  const [serverError, setServerError] = useState<string | null>(null);

  const handleSubmit = async (data: PostFormData): Promise<void> => {
    setFieldErrors({});
    setServerError(null);

    try {
      if (isEditing && postId) {
        await updatePost.mutateAsync({ id: postId, data });
      } else {
        await createPost.mutateAsync(data);
      }

      if (onSuccess) {
        onSuccess();
      } else {
        navigate('/posts');
      }
    } catch (err) {
      const parsed = parseAxiosError(err);
      setServerError(parsed.serverError);
      setFieldErrors(parsed.fieldErrors);
    }
  };

  const handleUploadImage = async (file: File): Promise<string | undefined> => {
    try {
      return await uploadImage.mutateAsync(file);
    } catch (err) {
      const parsed = parseAxiosError(err);
      setServerError(parsed.serverError || 'Failed to upload image.');
      return undefined;
    }
  };

  const handleRemoveImage = async (url: string): Promise<void> => {
    try {
      await deleteImage.mutateAsync(url);
      setServerError(null);
    } catch (err) {
      const parsed = parseAxiosError(err);
      setServerError(parsed.serverError || 'Failed to remove image from server.');
    }
  };

  const clearErrors = () => {
    setFieldErrors({});
    setServerError(null);
  };

  return {
    fieldErrors,
    serverError,
    handleSubmit,
    handleUploadImage,
    handleRemoveImage,
    clearErrors,
    isSubmitting: createPost.isPending || updatePost.isPending,
    isUploadingImage: uploadImage.isPending,
    isRemovingImage: deleteImage.isPending,
  };
}