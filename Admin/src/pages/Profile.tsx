import { useProfile, useUpdateProfile } from '@/features/profile';
import { ProfileForm } from '@/features/profile/components/ProfileForm';
import { UpdateProfilePayload } from '@/features/profile/types/profile';

export default function ProfilePage() {
  const { data: profile, isLoading: isFetching, isError } = useProfile();
  const { mutate: updateProfile, isPending: isUpdating } = useUpdateProfile();

  if (isError) {
    return (
      <div className="p-4">
        <div className="alert alert-danger">
          Failed to load profile. Please try again later.
        </div>
      </div>
    );
  }

  if (isFetching || !profile) {
    return (
      <div className="p-4">
        <div className="card">
          <div className="card-body">
            <div className="placeholder-glow">
              <span className="placeholder col-6 mb-3"></span>
              <span className="placeholder col-12 mb-3" style={{ height: '40px' }}></span>
              <span className="placeholder col-12 mb-3" style={{ height: '100px' }}></span>
              <span className="placeholder col-12 mb-3" style={{ height: '40px' }}></span>
            </div>
          </div>
        </div>
      </div>
    );
  }

  const handleUpdate = (payload: UpdateProfilePayload) => {
    updateProfile(payload);
  };

  return (
    <div className="p-4">
      <div className="mb-4">
        <h2 className="mb-1">Profile Settings</h2>
        <p className="text-muted">Update your professional information, bio, and social links.</p>
      </div>
      
      <div className="card">
        <div className="card-body">
          <ProfileForm 
            initialData={profile} 
            isSubmitting={isUpdating} 
            onSubmit={handleUpdate} 
          />
        </div>
      </div>
    </div>
  );
}