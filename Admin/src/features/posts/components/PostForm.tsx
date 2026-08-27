import { usePostFormState } from '../hooks/usePostFormState';
import type { PostFormData } from '../types/post';
import type { Category } from '@/features/categories/types/category';
import type { Tag } from '@/features/tags/types/tag';
import TitleField from './TitleField';
import BodyField from './BodyField';
import ExcerptField from './ExcerptField';
import FeaturedImageField from './FeaturedImageField';
import PublishSettings from './PublishSettings';
import CategorySelect from './CategorySelect';
import TagSelect from './TagSelect';

interface PostFormProps {
  initialData?: Partial<PostFormData>;
  categories: Category[];
  tags: Tag[];
  onSubmit: (data: PostFormData) => void;
  isLoading: boolean;
  serverError: string | null;
  fieldErrors: Record<string, string>;
  onUploadImage: (file: File) => Promise<string | undefined>;
  onRemoveImage: (url: string) => Promise<void>;
  isUploadingImage: boolean;
  isRemovingImage: boolean;
}

export default function PostForm({
  initialData,
  categories,
  tags,
  onSubmit,
  isLoading,
  serverError,
  fieldErrors,
  onUploadImage,
  onRemoveImage,
  isUploadingImage,
  isRemovingImage,
}: PostFormProps) {
  const {
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
  } = usePostFormState(initialData);

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    onSubmit({
      title,
      body,
      excerpt: excerpt || undefined,
      featured_image: featuredImage || undefined,
      is_published: isPublished,
      is_editors_pick: isEditorsPick,
      category_id: categoryId || undefined,
      tag_ids: tagIds.length > 0 ? tagIds : undefined,
    });
  };

  const handleRemoveFeaturedImage = async () => {
    if (!featuredImage) return;
    await onRemoveImage(featuredImage);
    setFeaturedImage('');
  };

  const hasFieldErrors = Object.keys(fieldErrors).length > 0;

  return (
    <form onSubmit={handleSubmit}>
      {serverError && !hasFieldErrors && (
        <div className="alert alert-danger">{serverError}</div>
      )}

      <TitleField
        value={title}
        onChange={setTitle}
        error={fieldErrors.title}
      />

      <BodyField
        value={body}
        onChange={setBody}
        onImageUpload={onUploadImage}
        isUploadingImage={isUploadingImage}
        error={fieldErrors.body}
      />

      <div className="row mb-3">
        <ExcerptField
          value={excerpt}
          onChange={setExcerpt}
          error={fieldErrors.excerpt}
        />
        <FeaturedImageField
          value={featuredImage}
          onChange={setFeaturedImage}
          onUpload={onUploadImage}
          onRemove={handleRemoveFeaturedImage}
          isUploading={isUploadingImage}
          isRemoving={isRemovingImage}
          error={fieldErrors.featured_image}
        />
      </div>

      <div className="row mb-3">
        <CategorySelect
          categories={categories}
          value={categoryId}
          onChange={setCategoryId}
          error={fieldErrors.category_id}
        />
        <PublishSettings
          isPublished={isPublished}
          onPublishedChange={setIsPublished}
          isEditorsPick={isEditorsPick}
          onEditorsPickChange={setIsEditorsPick}
        />
      </div>

      <TagSelect
        tags={tags}
        selectedIds={tagIds}
        onChange={setTagIds}
        error={fieldErrors.tag_ids}
      />

      <div className="d-flex justify-content-end">
        <button type="submit" className="btn btn-primary" disabled={isLoading}>
          {isLoading ? (
            <>
              <span className="spinner-border spinner-border-sm me-2" />
              Saving...
            </>
          ) : (
            'Save Post'
          )}
        </button>
      </div>
    </form>
  );
}