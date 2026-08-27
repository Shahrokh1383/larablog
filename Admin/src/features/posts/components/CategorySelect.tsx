import type { Category } from '@/features/categories/types/category';

interface CategorySelectProps {
  categories: Category[];
  value: string;
  onChange: (value: string) => void;
  error?: string;
}

export default function CategorySelect({
  categories,
  value,
  onChange,
  error,
}: CategorySelectProps) {
  return (
    <div className="col-md-4">
      <label className="form-label">Category</label>
      <select
        className={`form-select ${error ? 'is-invalid' : ''}`}
        value={value}
        onChange={(e) => onChange(e.target.value)}
      >
        <option value="">None</option>
        {categories.map((cat) => (
          <option key={cat.id} value={cat.id}>
            {cat.name}
          </option>
        ))}
      </select>
      {error && <div className="invalid-feedback d-block">{error}</div>}
    </div>
  );
}