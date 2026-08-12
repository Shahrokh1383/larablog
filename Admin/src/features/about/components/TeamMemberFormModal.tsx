import { useState, useEffect, useRef } from 'react';
import type { AdminUser } from '@/features/users/types/user';
import type { TeamMember } from '../types/about';

interface Props {
  isOpen: boolean;
  member: TeamMember | null; // null for new
  eligibleUsers: AdminUser[];
  isLoading: boolean;
  onClose: () => void;
  onSubmit: (formData: FormData) => void;
}

export default function TeamMemberFormModal({ isOpen, member, eligibleUsers, isLoading, onClose, onSubmit }: Props) {
  const formRef = useRef<HTMLFormElement>(null);
  const [photoPreview, setPhotoPreview] = useState<string | null>(member?.photo || null);

  useEffect(() => {
    if (member) {
      setPhotoPreview(member.photo);
    } else {
      setPhotoPreview(null);
    }
  }, [member]);

  if (!isOpen) return null;

const handleSubmit = (e: React.SyntheticEvent<HTMLFormElement>) => {
  e.preventDefault();
  if (!formRef.current) return;
  const formData = new FormData(formRef.current);

  const photo = formData.get('photo');
  if (!(photo instanceof File)) {
    formData.delete('photo');
  }

  onSubmit(formData);
};

  const handlePhotoChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (file) {
      setPhotoPreview(URL.createObjectURL(file));
    }
  };

  return (
    <div className="modal fade show d-block" tabIndex={-1} style={{ backgroundColor: 'rgba(0,0,0,0.5)' }}>
      <div className="modal-dialog">
        <div className="modal-content">
          <div className="modal-header">
            <h5 className="modal-title">{member ? 'Edit Team Member' : 'Add Team Member'}</h5>
            <button type="button" className="btn-close" onClick={onClose}></button>
          </div>
          <form ref={formRef} onSubmit={handleSubmit}>
            <div className="modal-body">
              <div className="mb-3">
                <label className="form-label">User (Admin/Editor/Author)</label>
                <select className="form-select" name="user_id" defaultValue={member?.user_id || ''} required>
                  <option value="" disabled>Select user...</option>
                  {eligibleUsers.map((u) => (
                    <option key={u.id} value={u.id}>{u.name} ({u.roles[0]})</option>
                  ))}
                </select>
              </div>
              <div className="mb-3">
                <label className="form-label">Display Name (optional)</label>
                <input type="text" className="form-control" name="display_name" defaultValue={member?.display_name || ''} />
              </div>
              <div className="mb-3">
                <label className="form-label">Position</label>
                <input type="text" className="form-control" name="position" defaultValue={member?.position || ''} required />
              </div>
              <div className="mb-3">
                <label className="form-label">Bio</label>
                <textarea className="form-control" name="bio" rows={3} defaultValue={member?.bio || ''} />
              </div>
              <div className="mb-3">
                <label className="form-label">Photo</label>
                <input type="file" className="form-control" name="photo" accept="image/*" onChange={handlePhotoChange} />
                {photoPreview && (
                  <div className="mt-2">
                    <img src={photoPreview} alt="Preview" className="rounded-circle" width="60" height="60" style={{ objectFit: 'cover' }} />
                  </div>
                )}
              </div>
              <div className="row">
                <div className="col-md-6 mb-3">
                  <label className="form-label">Sort Order</label>
                  <input type="number" className="form-control" name="sort_order" defaultValue={member?.sort_order || 0} min="0" />
                </div>
                <div className="col-md-6 mb-3">
                  <div className="form-check mt-4">
                    <input className="form-check-input" type="checkbox" name="is_active" defaultChecked={member ? member.is_active : true} />
                    <label className="form-check-label">Active</label>
                  </div>
                </div>
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