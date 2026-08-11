import { useState, useRef } from 'react';
import type { PostFormData } from '../types/post';
import type { Category } from '@/features/categories/types/category';
import type { Tag } from '@/features/tags/types/tag';
import { postsApi } from '../api/postsApi';
import RichTextEditor from './RichTextEditor';
import MultiSelectTags from './MultiSelectTags';

interface PostFormProps {
  initialData?: Partial<PostFormData>;
  categories: Category[];
  tags: Tag[];
  onSubmit: (data: PostFormData) => void;
  isLoading: boolean;
  serverError: string | null;
}

export default function PostForm({
  initialData,
  categories,
  tags,
  onSubmit,
  isLoading,
  serverError,
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
  const [isDeletingImage, setIsDeletingImage] = useState(false);

  const handleFeaturedImageUpload = async (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (!file) return;
    try {
      const url = await postsApi.uploadImage(file);
      setFeaturedImage(url);
    } catch (error) {
      alert('Failed to upload featured image.');
    }
  };

  const handleRemoveFeaturedImage = async () => {
    if (!featuredImage) return;
    
    setIsDeletingImage(true);
    try {
      await postsApi.deleteImage(featuredImage);
      setFeaturedImage('');
    } catch (error) {
      alert('Failed to delete image from server.');
    } finally {
      setIsDeletingImage(false);
    }
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

  return (
    <form onSubmit={handleSubmit}>
      {serverError && <div className="alert alert-danger">{serverError}</div>}

      <div className="mb-3">
        <label className="form-label">Title</label>
        <input type="text" className="form-control" value={title} onChange={(e) => setTitle(e.target.value)} required />
      </div>

      <div className="mb-3">
        <label className="form-label">Body</label>
        <RichTextEditor value={body} onChange={setBody} />
      </div>

      <div className="row mb-3">
        <div className="col-md-6">
          <label className="form-label">Excerpt</label>
          <textarea className="form-control" rows={3} value={excerpt} onChange={(e) => setExcerpt(e.target.value)} />
        </div>
        <div className="col-md-6">
          <label className="form-label">Featured Image</label>
          <input type="url" className="form-control mb-2" value={featuredImage} onChange={(e) => setFeaturedImage(e.target.value)} placeholder="Image URL" />
          
          <div className="d-flex align-items-center gap-2">
            <button type="button" className="btn btn-sm btn-outline-secondary" onClick={() => fileInputRef.current?.click()}>
              <i className="fas fa-upload me-1"></i> Upload Image
            </button>
            {featuredImage && (
              <button 
                type="button" 
                className="btn btn-sm btn-outline-danger" 
                onClick={handleRemoveFeaturedImage}
                disabled={isDeletingImage}
              >
                {isDeletingImage ? (
                  <span className="spinner-border spinner-border-sm" />
                ) : (
                  <><i className="fas fa-trash me-1"></i> Remove</>
                )}
              </button>
            )}
            <input type="file" ref={fileInputRef} className="d-none" accept="image/*" onChange={handleFeaturedImageUpload} />
          </div>
          
          {featuredImage && (
            <div className="mt-2 position-relative">
              <img src={featuredImage} alt="Featured" className="img-fluid rounded" style={{ maxHeight: '100px' }} />
            </div>
          )}
        </div>
      </div>

      <div className="row mb-3">
        <div className="col-md-4">
          <label className="form-label">Category</label>
          <select className="form-select" value={categoryId} onChange={(e) => setCategoryId(e.target.value)}>
            <option value="">None</option>
            {categories.map((cat) => <option key={cat.id} value={cat.id}>{cat.name}</option>)}
          </select>
        </div>
        <div className="col-md-4">
          <div className="form-check mt-4">
            <input type="checkbox" className="form-check-input" id="is_published" checked={isPublished} onChange={(e) => setIsPublished(e.target.checked)} />
            <label className="form-check-label" htmlFor="is_published">Publish immediately</label>
          </div>
          <div className='from-check mt-2'>
            <input
              type="checkbox"
              className="form-check-input"
              id="is_editors_pick"
              checked={isEditorsPick}
              onChange={(e) => setIsEditorsPick(e.target.checked)}
            />
            <label className="form-check-label" htmlFor="is_editors_pick">
              Editor’s Pick
            </label>
          </div>
        </div>
      </div>

      <div className="mb-3">
        <label className="form-label">Tags</label>
        <MultiSelectTags tags={tags} selectedIds={tagIds} onChange={setTagIds} />
      </div>

      <div className="d-flex justify-content-end">
        <button type="submit" className="btn btn-primary" disabled={isLoading}>
          {isLoading ? (<><span className="spinner-border spinner-border-sm me-2" />Saving...</>) : 'Save Post'}
        </button>
      </div>
    </form>
  );
}