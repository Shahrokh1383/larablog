// Admin/src/features/posts/components/PostForm.tsx
import { useState, useRef, useEffect } from 'react';
import type { PostFormData } from '../types/post';
import type { Category } from '@/features/categories/types/category';
import type { Tag } from '@/features/tags/types/tag';
import RichTextEditor from './RichTextEditor';
import MultiSelectTags from './MultiSelectTags';

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
  const [title, setTitle] = useState(initialData?.title ?? '');
  const [body, setBody] = useState(initialData?.body ?? '');
  const [excerpt, setExcerpt] = useState(initialData?.excerpt ?? '');
  const [featuredImage, setFeaturedImage] = useState(initialData?.featured_image ?? '');
  const [isPublished, setIsPublished] = useState(initialData?.is_published ?? false);
  const [isEditorsPick, setIsEditorsPick] = useState(initialData?.is_editors_pick ?? false);
  const [categoryId, setCategoryId] = useState(initialData?.category_id ?? '');
  const [tagIds, setTagIds] = useState<string[]>(initialData?.tag_ids ?? []);
  
  const fileInputRef = useRef<HTMLInputElement>(null);

  // Sync form state when initialData changes (e.g., loading existing post)
  useEffect(() => {
    if (initialData) {
      setTitle(initialData.title ?? '');
      setBody(initialData.body ?? '');
      setExcerpt(initialData.excerpt ?? '');
      setFeaturedImage(initialData.featured_image ?? '');
      setIsPublished(initialData.is_published ?? false);
      setIsEditorsPick(initialData.is_editors_pick ?? false);
      setCategoryId(initialData.category_id ?? '');
      setTagIds(initialData.tag_ids ?? []);
    }
  }, [initialData]);

  const handleFeaturedImageUpload = async (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (!file) return;
    
    const url = await onUploadImage(file);
    if (url) {
      setFeaturedImage(url);
    }
  };

  const handleRemoveFeaturedImage = async () => {
    if (!featuredImage) return;
    await onRemoveImage(featuredImage);
    setFeaturedImage('');
  };

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

  const hasFieldErrors = Object.keys(fieldErrors).length > 0;

  return (
    <form onSubmit={handleSubmit}>
      {serverError && !hasFieldErrors && (
        <div className="alert alert-danger">{serverError}</div>
      )}

      <div className="mb-3">
        <label className="form-label">Title</label>
        <input 
          type="text" 
          className={`form-control ${fieldErrors.title ? 'is-invalid' : ''}`}
          value={title} 
          onChange={(e) => setTitle(e.target.value)} 
          required 
        />
        {fieldErrors.title && (
          <div className="invalid-feedback d-block">{fieldErrors.title}</div>
        )}
      </div>

      <div className="mb-3">
        <label className="form-label">Body</label>
        <RichTextEditor 
          value={body} 
          onChange={setBody}
          onImageUpload={onUploadImage}
          isUploadingImage={isUploadingImage}
        />
        {fieldErrors.body && (
          <div className="text-danger small mt-1">{fieldErrors.body}</div>
        )}
      </div>

      <div className="row mb-3">
        <div className="col-md-6">
          <label className="form-label">Excerpt</label>
          <textarea 
            className={`form-control ${fieldErrors.excerpt ? 'is-invalid' : ''}`}
            rows={3} 
            value={excerpt} 
            onChange={(e) => setExcerpt(e.target.value)} 
          />
          {fieldErrors.excerpt && (
            <div className="invalid-feedback d-block">{fieldErrors.excerpt}</div>
          )}
        </div>
        <div className="col-md-6">
          <label className="form-label">Featured Image</label>
          <input 
            type="url" 
            className={`form-control mb-2 ${fieldErrors.featured_image ? 'is-invalid' : ''}`}
            value={featuredImage} 
            onChange={(e) => setFeaturedImage(e.target.value)} 
            placeholder="Image URL" 
            disabled={isUploadingImage} 
          />
          {fieldErrors.featured_image && (
            <div className="invalid-feedback d-block mb-2">{fieldErrors.featured_image}</div>
          )}
          
          <div className="d-flex align-items-center gap-2">
            <button 
              type="button" 
              className="btn btn-sm btn-outline-secondary" 
              onClick={() => fileInputRef.current?.click()} 
              disabled={isUploadingImage}
            >
              {isUploadingImage ? (
                <span className="spinner-border spinner-border-sm" />
              ) : (
                <><i className="fas fa-upload me-1"></i> Upload Image</>
              )}
            </button>
            {featuredImage && (
              <button 
                type="button" 
                className="btn btn-sm btn-outline-danger" 
                onClick={handleRemoveFeaturedImage}
                disabled={isRemovingImage}
              >
                {isRemovingImage ? (
                  <span className="spinner-border spinner-border-sm" />
                ) : (
                  <><i className="fas fa-trash me-1"></i> Remove</>
                )}
              </button>
            )}
            <input 
              type="file" 
              ref={fileInputRef} 
              className="d-none" 
              accept="image/*" 
              onChange={handleFeaturedImageUpload} 
            />
          </div>
          
          {featuredImage && (
            <div className="mt-2 position-relative">
              <img 
                src={featuredImage} 
                alt="Featured" 
                className="img-fluid rounded" 
                style={{ maxHeight: '100px' }} 
              />
            </div>
          )}
        </div>
      </div>

      <div className="row mb-3">
        <div className="col-md-4">
          <label className="form-label">Category</label>
          <select 
            className={`form-select ${fieldErrors.category_id ? 'is-invalid' : ''}`}
            value={categoryId} 
            onChange={(e) => setCategoryId(e.target.value)}
          >
            <option value="">None</option>
            {categories.map((cat) => (
              <option key={cat.id} value={cat.id}>{cat.name}</option>
            ))}
          </select>
          {fieldErrors.category_id && (
            <div className="invalid-feedback d-block">{fieldErrors.category_id}</div>
          )}
        </div>
        <div className="col-md-4">
          <div className="form-check mt-4">
            <input 
              type="checkbox" 
              className="form-check-input" 
              id="is_published" 
              checked={isPublished} 
              onChange={(e) => setIsPublished(e.target.checked)} 
            />
            <label className="form-check-label" htmlFor="is_published">
              Publish immediately
            </label>
          </div>
          <div className="form-check mt-2">
            <input
              type="checkbox"
              className="form-check-input"
              id="is_editors_pick"
              checked={isEditorsPick}
              onChange={(e) => setIsEditorsPick(e.target.checked)}
            />
            <label className="form-check-label" htmlFor="is_editors_pick">
              Editor's Pick
            </label>
          </div>
        </div>
      </div>

      <div className="mb-3">
        <label className="form-label">Tags</label>
        <MultiSelectTags tags={tags} selectedIds={tagIds} onChange={setTagIds} />
        {fieldErrors.tag_ids && (
          <div className="text-danger small mt-1">{fieldErrors.tag_ids}</div>
        )}
      </div>

      <div className="d-flex justify-content-end">
        <button type="submit" className="btn btn-primary" disabled={isLoading}>
          {isLoading ? (
            <><span className="spinner-border spinner-border-sm me-2" />Saving...</>
          ) : (
            'Save Post'
          )}
        </button>
      </div>
    </form>
  );
}