import { useState } from 'react';
import type { TagFormData } from '../types/tag';

interface TagFormModalProps {
  show: boolean;
  title: string;
  initialName?: string;
  onSubmit: (data: TagFormData) => void;
  isLoading: boolean;
  serverError: string | null;
  onClose: () => void;
}

export default function TagFormModal({
  show,
  title,
  initialName = '',
  onSubmit,
  isLoading,
  serverError,
  onClose,
}: TagFormModalProps) {
  const [name, setName] = useState(initialName);

  if (!show) return null;

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    onSubmit({ name });
  };

  return (
    <div className="modal d-block" tabIndex={-1} style={{ backgroundColor: 'rgba(0,0,0,0.5)' }}>
      <div className="modal-dialog modal-dialog-centered">
        <div className="modal-content">
          <form onSubmit={handleSubmit}>
            <div className="modal-header">
              <h5 className="modal-title">{title}</h5>
              <button type="button" className="btn-close" onClick={onClose}></button>
            </div>
            <div className="modal-body">
              {serverError && <div className="alert alert-danger">{serverError}</div>}
              <div className="mb-3">
                <label className="form-label">Name</label>
                <input type="text" className="form-control" value={name} onChange={(e) => setName(e.target.value)} required autoFocus />
              </div>
            </div>
            <div className="modal-footer">
              <button type="button" className="btn btn-secondary" onClick={onClose}>Cancel</button>
              <button type="submit" className="btn btn-primary" disabled={isLoading}>
                {isLoading ? 'Saving...' : 'Save'}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  );
}