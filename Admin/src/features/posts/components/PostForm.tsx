import { useState } from 'react';
import type { PostFormData } from '../types/post';
import type { Category } from '@/features/categories/types/category';
import type { Tag } from '@/features/tags/types/tag';

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
  const [categoryId, setCategoryId] = useState(initialData?.category_id ?? '');
  const [tagIds, setTagIds] = useState<string[]>(initialData?.tag_ids ?? []);

  const handleTagToggle = (tagId: string) => {
    setTagIds((prev) =>
      prev.includes(tagId) ? prev.filter((id) => id !== tagId) : [...prev, tagId]
    );
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    onSubmit({
      title,
      body,
      excerpt: excerpt || undefined,
      featured_image: featuredImage || undefined,
      is_published: isPublished,
      category_id: categoryId || undefined,
      tag_ids: tagIds.length > 0 ? tagIds : undefined,
    });
  };

  return (
    <form onSubmit={handleSubmit}>
      {serverError && (
        <div className="alert alert-danger">{serverError}</div>
      )}

      <div className="mb-3">
        <label className="form-label">Title</label>
        <input
          type="text"
          className="form-control"
          value={title}
          onChange={(e) => setTitle(e.target.value)}
          required
        />
      </div>

      <div className="mb-3">
        <label className="form-label">Body</label>
        <textarea
          className="form-control"
          rows={10}
          value={body}
          onChange={(e) => setBody(e.target.value)}
          required
        />
      </div>

      <div className="row mb-3">
        <div className="col-md-6">
          <label className="form-label">Excerpt</label>
          <textarea
            className="form-control"
            rows={3}
            value={excerpt}
            onChange={(e) => setExcerpt(e.target.value)}
          />
        </div>
        <div className="col-md-6">
          <label className="form-label">Featured Image URL</label>
          <input
            type="url"
            className="form-control"
            value={featuredImage}
            onChange={(e) => setFeaturedImage(e.target.value)}
          />
        </div>
      </div>

      <div className="row mb-3">
        <div className="col-md-4">
          <label className="form-label">Category</label>
          <select
            className="form-select"
            value={categoryId}
            onChange={(e) => setCategoryId(e.target.value)}
          >
            <option value="">None</option>
            {categories.map((cat) => (
              <option key={cat.id} value={cat.id}>{cat.name}</option>
            ))}
          </select>
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
        </div>
      </div>

      <div className="mb-3">
        <label className="form-label">Tags</label>
        <div className="d-flex flex-wrap gap-2">
          {tags.map((tag) => (
            <div key={tag.id} className="form-check">
              <input
                className="form-check-input"
                type="checkbox"
                id={`tag-${tag.id}`}
                checked={tagIds.includes(tag.id)}
                onChange={() => handleTagToggle(tag.id)}
              />
              <label className="form-check-label" htmlFor={`tag-${tag.id}`}>
                {tag.name}
              </label>
            </div>
          ))}
        </div>
      </div>

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