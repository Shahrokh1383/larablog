import { useRef } from 'react';

interface FeaturedImageFieldProps {
  value: string;
  onChange: (value: string) => void;
  onUpload: (file: File) => Promise<string | undefined>;
  onRemove: () => void;
  isUploading: boolean;
  isRemoving: boolean;
  error?: string;
}

export default function FeaturedImageField({
  value,
  onChange,
  onUpload,
  onRemove,
  isUploading,
  isRemoving,
  error,
}: FeaturedImageFieldProps) {
  const fileInputRef = useRef<HTMLInputElement>(null);

  const handleUpload = async (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (!file) return;
    const url = await onUpload(file);
    if (url) onChange(url);
  };

  return (
    <div className="col-md-6">
      <label className="form-label">Featured Image</label>
      <input
        type="url"
        className={`form-control mb-2 ${error ? 'is-invalid' : ''}`}
        value={value}
        onChange={(e) => onChange(e.target.value)}
        placeholder="Image URL"
        disabled={isUploading}
      />
      {error && <div className="invalid-feedback d-block mb-2">{error}</div>}

      <div className="d-flex align-items-center gap-2">
        <button
          type="button"
          className="btn btn-sm btn-outline-secondary"
          onClick={() => fileInputRef.current?.click()}
          disabled={isUploading}
        >
          {isUploading ? (
            <span className="spinner-border spinner-border-sm" />
          ) : (
            <>
              <i className="fas fa-upload me-1"></i> Upload Image
            </>
          )}
        </button>
        {value && (
          <button
            type="button"
            className="btn btn-sm btn-outline-danger"
            onClick={onRemove}
            disabled={isRemoving}
          >
            {isRemoving ? (
              <span className="spinner-border spinner-border-sm" />
            ) : (
              <>
                <i className="fas fa-trash me-1"></i> Remove
              </>
            )}
          </button>
        )}
        <input
          type="file"
          ref={fileInputRef}
          className="d-none"
          accept="image/*"
          onChange={handleUpload}
        />
      </div>

      {value && (
        <div className="mt-2">
          <img
            src={value}
            alt="Featured"
            className="img-fluid rounded"
            style={{ maxHeight: '100px' }}
          />
        </div>
      )}
    </div>
  );
}