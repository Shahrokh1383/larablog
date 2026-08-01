import { useState } from 'react';
import type { AdminUser, UpdatePasswordPayload } from '../types/user';

interface Props {
  user: AdminUser | null;
  isLoading: boolean;
  onClose: () => void;
  onSubmit: (payload: UpdatePasswordPayload) => void;
}

export default function EditUserPasswordModal({ user, isLoading, onClose, onSubmit }: Props) {
  const [password, setPassword] = useState('');
  const [passwordConfirmation, setPasswordConfirmation] = useState('');

  if (!user) return null;

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    onSubmit({ password, password_confirmation: passwordConfirmation });
  };

  return (
    <div className="modal fade show d-block" tabIndex={-1} style={{ backgroundColor: 'rgba(0,0,0,0.5)' }}>
      <div className="modal-dialog">
        <div className="modal-content">
          <div className="modal-header">
            <h5 className="modal-title">Reset Password for {user.name}</h5>
            <button type="button" className="btn-close" onClick={onClose}></button>
          </div>
          <form onSubmit={handleSubmit}>
            <div className="modal-body">
              <div className="mb-3">
                <label className="form-label">New Password</label>
                <input 
                  type="password" 
                  className="form-control" 
                  value={password}
                  onChange={(e) => setPassword(e.target.value)}
                  required
                  minLength={8}
                />
              </div>
              <div className="mb-3">
                <label className="form-label">Confirm Password</label>
                <input 
                  type="password" 
                  className="form-control" 
                  value={passwordConfirmation}
                  onChange={(e) => setPasswordConfirmation(e.target.value)}
                  required
                />
              </div>
            </div>
            <div className="modal-footer">
              <button type="button" className="btn btn-secondary" onClick={onClose}>Close</button>
              <button type="submit" className="btn btn-warning" disabled={isLoading}>
                {isLoading ? 'Updating...' : 'Update Password'}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  );
}