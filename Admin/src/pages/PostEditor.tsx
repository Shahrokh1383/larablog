import { useParams, useNavigate } from 'react-router-dom';
import { usePostDetail, usePostForm } from '@/features/posts';
import { useCategories } from '@/features/categories';
import { useTags } from '@/features/tags';
import PostForm from '@/features/posts/components/PostForm';

export default function PostEditorPage() {
  const { id } = useParams<{ id: string }>();
  const navigate = useNavigate();
  const isEditing = !!id;

  const { data: post, isLoading: postLoading } = usePostDetail(id);

  const {
    fieldErrors,
    serverError,
    handleSubmit,
    handleUploadImage,
    handleRemoveImage,
    isSubmitting,
    isUploadingImage,
    isRemovingImage,
  } = usePostForm({ postId: id });

  const { data: categoriesPaginated } = useCategories({ perPage: 100 });
  const categories = categoriesPaginated?.data ?? [];

  const { data: tagsPaginated } = useTags({ perPage: 100 });
  const tags = tagsPaginated?.data ?? [];

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
                key={post?.id ?? 'new'}
                initialData={initialData}
                categories={categories}
                tags={tags}
                onSubmit={handleSubmit}
                isLoading={isSubmitting}
                serverError={serverError}
                fieldErrors={fieldErrors}
                onUploadImage={handleUploadImage}
                onRemoveImage={handleRemoveImage}
                isUploadingImage={isUploadingImage}
                isRemovingImage={isRemovingImage}
              />
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}