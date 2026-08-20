import type { ValidationErrors } from '@/features/categories/hooks/useCategoryMutations';

interface EntityFormModalProps {
  show: boolean;
  title: string;
  name: string;
  onNameChange: (name: string) => void;
  onSubmit: (data: { name: string }) => void;
  isLoading: boolean;
  validationErrors?: ValidationErrors | null;
  onClose: () => void;
  entityIcon?: string;
}

export default function EntityFormModal({
  show,
  title,
  name,
  onNameChange,
  onSubmit,
  isLoading,
  validationErrors,
  onClose,
  entityIcon = 'folder',
}: EntityFormModalProps) {
  if (!show) return null;

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    onSubmit({ name });
  };

  const nameError = validationErrors?.name?.[0];

  return (
    <div className="modal d-block" tabIndex={-1} style={{ backgroundColor: 'rgba(0,0,0,0.5)' }}>
      <div className="modal-dialog modal-dialog-centered">
        <div className="modal-content">
          <form onSubmit={handleSubmit} noValidate>
            <div className="modal-header">
              <h5 className="modal-title">
                <i className={`fas fa-${entityIcon} me-2`}></i>
                {title}
              </h5>
              <button type="button" className="btn-close" onClick={onClose} disabled={isLoading}></button>
            </div>
            <div className="modal-body">
              <div className="mb-3">
                <label className="form-label">Name</label>
                <input
                  type="text"
                  className={`form-control ${nameError ? 'is-invalid' : ''}`}
                  value={name}
                  onChange={(e) => onNameChange(e.target.value)}
                  required
                  autoFocus
                  disabled={isLoading}
                  maxLength={255}
                />
                {nameError && (
                  <div className="invalid-feedback d-block">{nameError}</div>
                )}
              </div>
            </div>
            <div className="modal-footer">
              <button type="button" className="btn btn-secondary" onClick={onClose} disabled={isLoading}>
                Cancel
              </button>
              <button type="submit" className="btn btn-primary" disabled={isLoading || !name.trim()}>
                {isLoading ? (
                  <>
                    <span className="spinner-border spinner-border-sm me-2" />
                    Saving...
                  </>
                ) : (
                  'Save'
                )}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  );
}