import { useState, useEffect, useRef } from 'react';
import { Profile, UpdateProfilePayload, SocialLinks } from '../types/profile';
import { SocialLinksInput } from './SocialLinksInput';
import { profileApi } from '../api/profileApi';

interface ProfileFormProps {
  initialData: Profile;
  isSubmitting: boolean;
  onSubmit: (data: UpdateProfilePayload) => void;
}

export function ProfileForm({ initialData, isSubmitting, onSubmit }: ProfileFormProps) {
  const [name, setName] = useState(initialData.name || '');
  const [avatar, setAvatar] = useState(initialData.avatar || '');
  const [bio, setBio] = useState(initialData.bio || '');
  const [expertise, setExpertise] = useState(initialData.expertise || '');
  const [years, setYears] = useState(initialData.years_of_experience?.toString() || '');
  const [socialLinks, setSocialLinks] = useState<SocialLinks>(initialData.social_links || {});
  
  const fileInputRef = useRef<HTMLInputElement>(null);
  const [isDeletingAvatar, setIsDeletingAvatar] = useState(false);

  useEffect(() => {
    setName(initialData.name || '');
    setAvatar(initialData.avatar || '');
    setBio(initialData.bio || '');
    setExpertise(initialData.expertise || '');
    setYears(initialData.years_of_experience?.toString() || '');
    setSocialLinks(initialData.social_links || {});
  }, [initialData]);

  const handleAvatarUpload = async (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (!file) return;
    try {
      const url = await profileApi.uploadAvatar(file);
      setAvatar(url);
    } catch (error) {
      alert('Failed to upload avatar.');
    }
    if (fileInputRef.current) fileInputRef.current.value = '';
  };

  const handleRemoveAvatar = async () => {
    if (!avatar) return;
    
    setIsDeletingAvatar(true);
    try {
      await profileApi.deleteAvatar(avatar);
      setAvatar('');
    } catch (error) {
      alert('Failed to delete avatar from server.');
    } finally {
      setIsDeletingAvatar(false);
    }
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    onSubmit({
      name,
      avatar: avatar || null,
      bio: bio || null,
      expertise: expertise || null,
      years_of_experience: years ? parseInt(years, 10) : null,
      social_links: socialLinks,
    });
  };

  return (
    <form onSubmit={handleSubmit}>
      {/* Avatar Section */}
      <div className="mb-4">
        <label className="form-label d-block">Avatar</label>
        <div className="d-flex align-items-center gap-3">
          {avatar ? (
            <img 
              src={avatar} 
              alt="Avatar" 
              className="rounded-circle border" 
              style={{ width: '80px', height: '80px', objectFit: 'cover' }} 
            />
          ) : (
            <div 
              className="bg-light rounded-circle d-flex align-items-center justify-content-center border" 
              style={{ width: '80px', height: '80px' }}
            >
              <i className="fas fa-user text-muted fa-2x"></i>
            </div>
          )}
          
          <div className="d-flex flex-column gap-2">
            <button 
              type="button" 
              className="btn btn-sm btn-outline-primary" 
              onClick={() => fileInputRef.current?.click()}
            >
              <i className="fas fa-upload me-1"></i> {avatar ? 'Change Avatar' : 'Upload Avatar'}
            </button>
            
            {avatar && (
              <button 
                type="button" 
                className="btn btn-sm btn-outline-danger" 
                onClick={handleRemoveAvatar}
                disabled={isDeletingAvatar}
              >
                {isDeletingAvatar ? (
                  <span className="spinner-border spinner-border-sm" />
                ) : (
                  <><i className="fas fa-trash me-1"></i> Remove</>
                )}
              </button>
            )}
          </div>
        </div>
        <input 
          type="file" 
          ref={fileInputRef} 
          className="d-none" 
          accept="image/*" 
          onChange={handleAvatarUpload} 
        />
      </div>

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