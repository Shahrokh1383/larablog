import { useState, useEffect } from 'react';
import type { AdminUser, UpdateRolePayload } from '../types/user';

interface Props {
  user: AdminUser | null;
  isLoading: boolean;
  onClose: () => void;
  onSubmit: (payload: UpdateRolePayload) => void;
}

export default function EditUserRoleModal({ user, isLoading, onClose, onSubmit }: Props) {
  const [role, setRole] = useState('user');

  useEffect(() => {
    if (user) {
      setRole(user.roles[0] || 'user');
    }
  }, [user]);

  if (!user) return null;

  return (
    <div className="modal fade show d-block" tabIndex={-1} style={{ backgroundColor: 'rgba(0,0,0,0.5)' }}>
      <div className="modal-dialog">
        <div className="modal-content">
          <div className="modal-header">
            <h5 className="modal-title">Edit Role for {user.name}</h5>
            <button type="button" className="btn-close" onClick={onClose}></button>
          </div>
          <form onSubmit={(e) => { e.preventDefault(); onSubmit({ role }); }}>
            <div className="modal-body">
              <div className="mb-3">
                <label className="form-label">Role</label>
                <select className="form-select" value={role} onChange={(e) => setRole(e.target.value)}>
                  <option value="admin">Admin</option>
                  <option value="editor">Editor</option>
                  <option value="author">Author</option>
                  <option value="user">User</option>
                </select>
              </div>
            </div>
            <div className="modal-footer">
              <button type="button" className="btn btn-secondary" onClick={onClose}>Close</button>
              <button type="submit" className="btn btn-primary" disabled={isLoading}>
                {isLoading ? 'Saving...' : 'Save Changes'}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  );
}