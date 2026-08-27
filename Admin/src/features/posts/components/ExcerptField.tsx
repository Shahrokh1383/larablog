interface ExcerptFieldProps {
  value: string;
  onChange: (value: string) => void;
  error?: string;
}

export default function ExcerptField({ value, onChange, error }: ExcerptFieldProps) {
  return (
    <div className="col-md-6">
      <label className="form-label">Excerpt</label>
      <textarea
        className={`form-control ${error ? 'is-invalid' : ''}`}
        rows={3}
        value={value}
        onChange={(e) => onChange(e.target.value)}
      />
      {error && <div className="invalid-feedback d-block">{error}</div>}
    </div>
  );
}