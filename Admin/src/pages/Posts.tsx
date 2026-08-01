import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { usePosts, usePostMutations, PostDataTable, PostFormModal } from '@/features/posts';
import { useCategories } from '@/features/categories';
import { useTags } from '@/features/tags';
import type { Post, PostFormData } from '@/features/posts';
import { AxiosError } from 'axios';

export default function PostsPage() {
  const navigate = useNavigate();
  const { data: posts = [], isLoading, isError } = usePosts();
  const { data: categories = [] } = useCategories();
  const { data: tags = [] } = useTags();
  const { createPost, updatePost, deletePost } = usePostMutations();

  const [showModal, setShowModal] = useState(false);
  const [editingPost, setEditingPost] = useState<Post | null>(null);
  const [serverError, setServerError] = useState<string | null>(null);

  const openCreate = () => {
    setEditingPost(null);
    setServerError(null);
    setShowModal(true);
  };

  const openEdit = (post: Post) => {
    setEditingPost(post);
    setServerError(null);
    setShowModal(true);
  };

  const handleDelete = (post: Post) => {
    if (window.confirm(`Delete "${post.title}"?`)) {
      deletePost.mutate(post.id);
    }
  };

  const handleFormSubmit = (data: PostFormData) => {
    setServerError(null);
    if (editingPost) {
      updatePost.mutate(
        { id: editingPost.id, data },
        {
          onSuccess: () => setShowModal(false),
          onError: (err) => {
            const axiosErr = err as AxiosError<{ message: string }>;
            setServerError(axiosErr.response?.data?.message ?? 'Update failed');
          },
        }
      );
    } else {
      createPost.mutate(data, {
        onSuccess: () => setShowModal(false),
        onError: (err) => {
          const axiosErr = err as AxiosError<{ message: string }>;
          setServerError(axiosErr.response?.data?.message ?? 'Create failed');
        },
      });
    }
  };

  return (
    <div className="container py-4">
      <div className="d-flex justify-content-between align-items-center mb-4">
        <h1>Posts</h1>
        <button className="btn btn-primary" onClick={openCreate}>
          <i className="fas fa-plus me-2"></i>New Post
        </button>
      </div>

      <div className="card shadow-sm">
        <div className="card-body">
          <PostDataTable
            posts={posts}
            isLoading={isLoading}
            isError={isError}
            onEdit={openEdit}
            onDelete={handleDelete}
          />
        </div>
      </div>

      <PostFormModal
        show={showModal}
        title={editingPost ? 'Edit Post' : 'Create Post'}
        initialData={
          editingPost
            ? {
                title: editingPost.title,
                body: editingPost.body,
                excerpt: editingPost.excerpt ?? '',
                featured_image: editingPost.featured_image ?? '',
                is_published: editingPost.is_published,
                category_id: editingPost.category?.id ?? '',
                tag_ids: editingPost.tags.map((t) => t.id),
              }
            : undefined
        }
        categories={categories}
        tags={tags}
        onSubmit={handleFormSubmit}
        isLoading={createPost.isPending || updatePost.isPending}
        serverError={serverError}
        onClose={() => setShowModal(false)}
      />
    </div>
  );
}