'use client';

import { useState, useEffect } from 'react';
import { useProfile } from '@/features/profile/hooks/useProfile';
import { useUpdateProfile } from '@/features/profile/hooks/useUpdateProfile';
import { useUploadAvatar } from '@/features/profile/hooks/useUploadAvatar';
import { useDeleteAvatar } from '@/features/profile/hooks/useDeleteAvatar';
import { useDeleteAccount } from '@/features/profile/hooks/useDeleteAccount';
import { useAuth } from '@/features/auth/context/AuthContext';

interface SettingsTabProps {
  hasBeenActive: boolean;
}

export default function SettingsTab({ hasBeenActive }: SettingsTabProps) {
  const { user: authUser } = useAuth();
  
  // Defer the profile fetch until the user actually visits the Settings tab
  const { data: profile, isError } = useProfile({ enabled: hasBeenActive });
  
  const updateProfile = useUpdateProfile();
  const uploadAvatar = useUploadAvatar();
  const deleteAvatar = useDeleteAvatar();
  const deleteAccount = useDeleteAccount();

  // Initialize with fallback data to prevent empty fields during initial load
  const [name, setName] = useState(profile?.name ?? authUser?.name ?? '');
  const [bio, setBio] = useState(profile?.bio ?? '');
  const [avatarUrl, setAvatarUrl] = useState<string | null>(profile?.avatar ?? authUser?.avatar ?? null);
  const [showDeleteConfirm, setShowDeleteConfirm] = useState(false);

  // Sync state when profile data finally arrives or changes
  useEffect(() => {
    if (profile) {
      setName(profile.name ?? authUser?.name ?? '');
      setBio(profile.bio ?? '');
      setAvatarUrl(profile.avatar ?? null);
    } else if (isError && authUser) {
      // Fallback to auth user data when profile fetch fails (e.g., no row yet)
      setName(authUser.name ?? '');
      setBio('');
      setAvatarUrl(authUser.avatar ?? null);
    }
  }, [profile, isError, authUser]);

  const handleAvatarChange = async (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (!file) return;
    try {
      const { url } = await uploadAvatar.mutateAsync(file);
      setAvatarUrl(url);
    } catch (err) {
      console.error('Avatar upload failed', err);
    }
  };

  const handleDeleteAvatar = async () => {
    if (!avatarUrl) return;
    try {
      await deleteAvatar.mutateAsync(avatarUrl);
      setAvatarUrl(null);
    } catch (err) {
      console.error('Avatar deletion failed', err);
    }
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    await updateProfile.mutateAsync({ name, avatar: avatarUrl, bio });
  };

  const handleDeleteAccount = async () => {
    await deleteAccount.mutateAsync();
  };

  return (
    <div className="tab-content active" id="settingsContent">
      <div className="dashboard-card">
        <div className="card-body">
          <h3 className="card-title mb-4">Edit Profile</h3>
          <form className="settings-form" onSubmit={handleSubmit}>
            <div className="row g-4">
              <div className="col-md-6">
                <label className="form-label">Full Name</label>
                <input type="text" className="form-control" value={name} onChange={(e) => setName(e.target.value)} />
              </div>
              <div className="col-md-6">
                <label className="form-label">Email Address</label>
                <input type="email" className="form-control" value={authUser?.email ?? ''} disabled />
              </div>
              <div className="col-12">
                <label className="form-label">Bio</label>
                <textarea className="form-control" rows={3} value={bio} onChange={(e) => setBio(e.target.value)} />
              </div>
              <div className="col-12">
                <label className="form-label">Profile Picture</label>
                <input type="file" className="form-control" accept="image/*" onChange={handleAvatarChange} disabled={uploadAvatar.isPending} />
                {avatarUrl && (
                  <div className="mt-2">
                    <button type="button" className="btn btn-outline-danger btn-sm" onClick={handleDeleteAvatar} disabled={deleteAvatar.isPending}>
                      {deleteAvatar.isPending ? 'Deleting...' : 'Remove current avatar'}
                    </button>
                  </div>
                )}
              </div>
              <div className="col-12">
                <button type="submit" className="btn btn-primary-custom" disabled={updateProfile.isPending}>
                  {updateProfile.isPending ? (
                    <><span className="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Saving...</>
                  ) : (
                    <><i className="fa-sharp fa-solid fa-floppy-disk"></i> Save Changes</>
                  )}
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>

      {/* Danger Zone */}
      <div className="dashboard-card mt-4">
        <div className="card-body">
          <h4 className="card-title text-danger">Danger Zone</h4>
          <p className="card-text text-muted">Once you delete your account, there is no going back. Please be certain.</p>
          {!showDeleteConfirm ? (
            <button className="btn btn-outline-danger" onClick={() => setShowDeleteConfirm(true)}>
              <i className="fa-sharp fa-solid fa-trash-can me-2"></i> Delete Account
            </button>
          ) : (
            <div className="border border-danger rounded p-3 bg-danger bg-opacity-10">
              <p className="mb-3">Are you sure you want to delete your account? This action cannot be undone.</p>
              <div className="d-flex gap-2">
                <button className="btn btn-danger" onClick={handleDeleteAccount} disabled={deleteAccount.isPending}>
                  {deleteAccount.isPending ? 'Deleting...' : 'Yes, delete my account'}
                </button>
                <button className="btn btn-outline-secondary" onClick={() => setShowDeleteConfirm(false)} disabled={deleteAccount.isPending}>
                  Cancel
                </button>
              </div>
            </div>
          )}
        </div>
      </div>
    </div>
  );
}