import { useParams, useNavigate } from 'react-router-dom';
import { useQuery } from '@tanstack/react-query';
import { postsApi, PostForm, postKeys, usePostMutations } from '@/features/posts';
import type { PostFormData, PostTag } from '@/features/posts';
import { useCategories } from '@/features/categories';
import { useTags } from '@/features/tags';
import { AxiosError } from 'axios';
import { useState } from 'react';

export default function PostEditorPage() {
  const { id } = useParams<{ id: string }>();
  const navigate = useNavigate();
  const isEditing = !!id;

  const { data: post, isLoading: postLoading } = useQuery({
    queryKey: postKeys.detail(id!),
    queryFn: () => postsApi.getById(id!),
    enabled: isEditing,
  });

  const { data: categories = [] } = useCategories();
  const { data: tags = [] } = useTags();
  const { createPost, updatePost } = usePostMutations();
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
        category_id: post.category?.id ?? '',
        tag_ids: post.tags.map((t: PostTag) => t.id),
      }
    : undefined;

  return (
    <div className="container py-4">
      <div className="row justify-content-center">
        <div className="col-lg-8">
          <div className="card shadow-sm">
            <div className="card-header bg-white">
              <h4 className="mb-0">{isEditing ? 'Edit Post' : 'Create New Post'}</h4>
            </div>
            <div className="card-body">
              <PostForm
                initialData={initialData}
                categories={categories}
                tags={tags}
                onSubmit={handleSubmit}
                isLoading={createPost.isPending || updatePost.isPending}
                serverError={serverError}
              />
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}