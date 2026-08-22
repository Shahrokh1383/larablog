import { useParams, useNavigate } from 'react-router-dom';
import { usePostDetail, usePostMutations, postsApi } from '@/features/posts';
import PostForm from '@/features/posts/components/PostForm';
import { useCategories } from '@/features/categories';
import { useTags } from '@/features/tags';
import type { PostFormData } from '@/features/posts';
import { AxiosError } from 'axios';
import { useState } from 'react';

export default function PostEditorPage() {
  const { id } = useParams<{ id: string }>();
  const navigate = useNavigate();
  const isEditing = !!id;

  const { data: post, isLoading: postLoading } = usePostDetail(id);

  // Fetch all categories and tags for the form dropdowns
  const { data: categoriesPaginated } = useCategories({ perPage: 1000 });
  const categories = categoriesPaginated?.data ?? [];

  const { data: tagsPaginated } = useTags({ perPage: 1000 });
  const tags = tagsPaginated?.data ?? [];

  const { createPost, updatePost, uploadImage, deleteImage } = usePostMutations();
  const [serverError, setServerError] = useState<string | null>(null);

  const handleSubmit = (data: PostFormData) => {
    setServerError(null);
    if (isEditing) {
      updatePost.mutate(
        { id: id!, data },
        {
          onSuccess: () => navigate('/posts'),
          onError: (err) => {
            const axiosErr = err as AxiosError<{ message: string }>;
            setServerError(axiosErr.response?.data?.message ?? 'Update failed');
          },
        }
      );
    } else {
      createPost.mutate(data, {
        onSuccess: () => navigate('/posts'),
        onError: (err) => {
          const axiosErr = err as AxiosError<{ message: string }>;
          setServerError(axiosErr.response?.data?.message ?? 'Create failed');
        },
      });
    }
  };

  const handleUploadImage = async (file: File) => {
    try {
      return await uploadImage.mutateAsync(file);
    } catch {
      setServerError('Failed to upload image.');
      return undefined;
    }
  };

  const handleRemoveImage = async (url: string) => {
    try {
      await deleteImage.mutateAsync(url);
      setServerError(null);
    } catch {
      setServerError('Failed to remove image from server.');
    }
  };

  if (isEditing && postLoading) {
    return (
      <div className="container py-5 text-center">
        <div className="spinner-border" />
      </div>
    );
  }

  const initialData = post
    ? {
        title: post.title,
        body: post.body,
        excerpt: post.excerpt ?? '',
        featured_image: post.featured_image ?? '',
        is_published: post.is_published,
        is_editors_pick: post.is_editors_pick,
        category_id: post.category?.id ?? '',
        tag_ids: post.tags.map((t) => t.id),
      }
    : undefined;

  return (
    <div className="container-fluid py-4">
      <div className="row justify-content-center">
        <div className="col-lg-10 col-xl-8">
          <div className="d-flex justify-content-between align-items-center mb-4">
            <h2 className="mb-0">{isEditing ? 'Edit Post' : 'Create New Post'}</h2>
            <button className="btn btn-outline-secondary" onClick={() => navigate('/posts')}>
              <i className="fas fa-arrow-left me-2"></i> Back to Posts
            </button>
          </div>
          
          <div className="card shadow-sm border-0">
            <div className="card-body p-4 p-md-5">
              <PostForm
                initialData={initialData}
                categories={categories}
                tags={tags}
                onSubmit={handleSubmit}
                isLoading={createPost.isPending || updatePost.isPending}
                serverError={serverError}
                onUploadImage={handleUploadImage}
                onRemoveImage={handleRemoveImage}
                isUploadingImage={uploadImage.isPending}
                isRemovingImage={deleteImage.isPending}
              />
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}