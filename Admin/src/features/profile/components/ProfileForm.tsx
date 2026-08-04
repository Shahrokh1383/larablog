import { useState, useEffect } from 'react';
import { Profile, UpdateProfilePayload, SocialLinks } from '../types/profile';
import { SocialLinksInput } from './SocialLinksInput';

interface ProfileFormProps {
  initialData: Profile;
  isSubmitting: boolean;
  onSubmit: (data: UpdateProfilePayload) => void;
}

export function ProfileForm({ initialData, isSubmitting, onSubmit }: ProfileFormProps) {
  // UI State (Form state is allowed in components)
  const [name, setName] = useState(initialData.name);
  const [bio, setBio] = useState(initialData.bio || '');
  const [expertise, setExpertise] = useState(initialData.expertise || '');
  const [years, setYears] = useState(initialData.years_of_experience?.toString() || '');
  const [socialLinks, setSocialLinks] = useState<SocialLinks>(initialData.social_links || {});

  // Sync internal state if initialData changes (e.g., after a successful mutation refetch)
  useEffect(() => {
    setName(initialData.name);
    setBio(initialData.bio || '');
    setExpertise(initialData.expertise || '');
    setYears(initialData.years_of_experience?.toString() || '');
    setSocialLinks(initialData.social_links || {});
  }, [initialData]);

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    onSubmit({
      name,
      bio: bio || null,
      expertise: expertise || null,
      years_of_experience: years ? parseInt(years, 10) : null,
      social_links: socialLinks,
    });
  };

  return (
    <form onSubmit={handleSubmit} className="space-y-6 max-w-2xl">
      <div>
        <label className="block text-sm font-medium text-gray-700">Full Name</label>
        <input
          type="text"
          value={name}
          onChange={(e) => setName(e.target.value)}
          required
          className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
        />
      </div>

      <div>
        <label className="block text-sm font-medium text-gray-700">Bio</label>
        <textarea
          value={bio}
          onChange={(e) => setBio(e.target.value)}
          rows={4}
          className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
        />
      </div>

      <div className="grid grid-cols-1 gap-y-6 sm:grid-cols-2 sm:gap-x-6">
        <div>
          <label className="block text-sm font-medium text-gray-700">Expertise</label>
          <input
            type="text"
            value={expertise}
            onChange={(e) => setExpertise(e.target.value)}
            placeholder="e.g., Senior UI/UX Designer"
            className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
          />
        </div>
        <div>
          <label className="block text-sm font-medium text-gray-700">Years of Experience</label>
          <input
            type="number"
            value={years}
            onChange={(e) => setYears(e.target.value)}
            min="0"
            max="80"
            className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
          />
        </div>
      </div>

      <SocialLinksInput links={socialLinks} onChange={setSocialLinks} />

      <div className="flex justify-end">
        <button
          type="submit"
          disabled={isSubmitting}
          className="inline-flex justify-center rounded-md border border-transparent bg-blue-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          {isSubmitting ? 'Saving...' : 'Save Profile'}
        </button>
      </div>
    </form>
  );
}