import { useProfile, useUpdateProfile } from '@/features/profile';
import { ProfileForm } from '@/features/profile/components/ProfileForm';
import { UpdateProfilePayload } from '@/features/profile/types/profile';

export default function ProfilePage() {
  const { data: profile, isLoading: isFetching } = useProfile();
  const { mutate: updateProfile, isPending: isUpdating } = useUpdateProfile();

  if (isFetching || !profile) {
    return (
      <div className="p-8">
        <div className="animate-pulse space-y-4 max-w-2xl">
          <div className="h-6 bg-gray-200 rounded w-1/4"></div>
          <div className="h-10 bg-gray-200 rounded"></div>
          <div className="h-24 bg-gray-200 rounded"></div>
        </div>
      </div>
    );
  }

  const handleUpdate = (payload: UpdateProfilePayload) => {
    updateProfile(payload);
  };

  return (
    <div className="p-8">
      <div className="mb-8">
        <h1 className="text-2xl font-bold text-gray-900">Profile Settings</h1>
        <p className="mt-1 text-sm text-gray-600">
          Update your professional information, bio, and social links.
        </p>
      </div>
      
      <ProfileForm 
        initialData={profile} 
        isSubmitting={isUpdating} 
        onSubmit={handleUpdate} 
      />
    </div>
  );
}