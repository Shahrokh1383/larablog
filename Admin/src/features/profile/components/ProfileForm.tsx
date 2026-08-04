import { useState, useEffect } from 'react';
import { Profile, UpdateProfilePayload, SocialLinks } from '../types/profile';
import { SocialLinksInput } from './SocialLinksInput';

interface ProfileFormProps {
  initialData: Profile;
  isSubmitting: boolean;
  onSubmit: (data: UpdateProfilePayload) => void;
}

export function ProfileForm({ initialData, isSubmitting, onSubmit }: ProfileFormProps) {
  const [name, setName] = useState(initialData.name || '');
  const [bio, setBio] = useState(initialData.bio || '');
  const [expertise, setExpertise] = useState(initialData.expertise || '');
  const [years, setYears] = useState(initialData.years_of_experience?.toString() || '');
  const [socialLinks, setSocialLinks] = useState<SocialLinks>(initialData.social_links || {});

  useEffect(() => {
    setName(initialData.name || '');
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
    <form onSubmit={handleSubmit}>
      <div className="mb-3">
        <label className="form-label">Full Name</label>
        <input
          type="text"
          className="form-control"
          value={name}
          onChange={(e) => setName(e.target.value)}
          required
        />
      </div>

      <div className="mb-3">
        <label className="form-label">Bio</label>
        <textarea
          className="form-control"
          rows={4}
          value={bio}
          onChange={(e) => setBio(e.target.value)}
          placeholder="Tell us about yourself..."
        />
      </div>

      <div className="row mb-3">
        <div className="col-md-6">
          <label className="form-label">Expertise</label>
          <input
            type="text"
            className="form-control"
            value={expertise}
            onChange={(e) => setExpertise(e.target.value)}
            placeholder="e.g., Senior UI/UX Designer"
          />
        </div>
        <div className="col-md-6">
          <label className="form-label">Years of Experience</label>
          <input
            type="number"
            className="form-control"
            value={years}
            onChange={(e) => setYears(e.target.value)}
            min="0"
            max="80"
          />
        </div>
      </div>

      <SocialLinksInput links={socialLinks} onChange={setSocialLinks} />

      <div className="d-flex justify-content-end mt-4">
        <button type="submit" className="btn btn-primary" disabled={isSubmitting}>
          {isSubmitting ? (
            <>
              <span className="spinner-border spinner-border-sm me-2" />
              Saving...
            </>
          ) : (
            'Save Profile'
          )}
        </button>
      </div>
    </form>
  );
}