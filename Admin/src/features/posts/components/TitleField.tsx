import React from 'react';

interface TitleFieldProps {
  value: string;
  onChange: (value: string) => void;
  error?: string;
}

export default function TitleField({ value, onChange, error }: TitleFieldProps) {
  return (
    <div className="mb-3">
      <label className="form-label">Title</label>
      <input
        type="text"
        className={`form-control ${error ? 'is-invalid' : ''}`}
        value={value}
        onChange={(e) => onChange(e.target.value)}
        required
      />
      {error && <div className="invalid-feedback d-block">{error}</div>}
    </div>
  );
}