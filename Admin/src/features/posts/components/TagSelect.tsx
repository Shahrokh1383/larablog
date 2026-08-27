import MultiSelectTags from './MultiSelectTags';
import type { Tag } from '@/features/tags/types/tag';

interface TagSelectProps {
  tags: Tag[];
  selectedIds: string[];
  onChange: (ids: string[]) => void;
  error?: string;
}

export default function TagSelect({ tags, selectedIds, onChange, error }: TagSelectProps) {
  return (
    <div className="mb-3">
      <label className="form-label">Tags</label>
      <MultiSelectTags tags={tags} selectedIds={selectedIds} onChange={onChange} />
      {error && <div className="text-danger small mt-1">{error}</div>}
    </div>
  );
}