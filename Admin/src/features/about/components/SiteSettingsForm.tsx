import { useState, useEffect } from 'react';
import type { SiteSettings } from '../types/about';

interface Props {
  settings: SiteSettings | undefined;
  isLoading: boolean;
  isSaving: boolean;
  isUploadingImage: boolean;
  isDeletingImage: boolean;
  onUploadStoryImage: (file: File) => void;
  onDeleteStoryImage: (url: string) => void;
  onSubmit: (data: Partial<SiteSettings>) => void;
}

export default function SiteSettingsForm({ 
  settings, isLoading, isSaving, isUploadingImage, isDeletingImage, onUploadStoryImage, onDeleteStoryImage, onSubmit 
}: Props) {
  const [phone, setPhone] = useState('');
  const [email1, setEmail1] = useState('');
  const [email2, setEmail2] = useState('');
  const [visitAddress, setVisitAddress] = useState('');
  const [storyImage, setStoryImage] = useState<string | null>(null);
  const [socialLinks, setSocialLinks] = useState({
    linkedin: '',
    github: '',
    twitter: '',
    instagram: '',
    dribbble: '',
    youtube: '',
    discord: '',
  });

  useEffect(() => {
    if (settings) {
      setPhone(settings.call_us_phone || '');
      setEmail1(settings.call_us_emails?.[0] || '');
      setEmail2(settings.call_us_emails?.[1] || '');
      setVisitAddress(settings.visit_address || '');
      setStoryImage(settings.story_image || null);
      setSocialLinks({
        linkedin: settings.social_links?.linkedin || '',
        github: settings.social_links?.github || '',
        twitter: settings.social_links?.twitter || '',
        instagram: settings.social_links?.instagram || '',
        dribbble: settings.social_links?.dribbble || '',
        youtube: settings.social_links?.youtube || '',
        discord: settings.social_links?.discord || '',
      });
    }
  }, [settings]);

  const handleImageChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    if (e.target.files && e.target.files.length > 0) {
      onUploadStoryImage(e.target.files[0]);
    }
  };

  const handleDeleteImage = () => {
    if (storyImage) {
      onDeleteStoryImage(storyImage);
    }
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    const call_us_emails = [email1, email2].filter(Boolean);
    onSubmit({
      call_us_phone: phone || null,
      call_us_emails,
      visit_address: visitAddress || null,
      story_image: storyImage,
      social_links: socialLinks,
    });
  };

  if (isLoading) return <div className="text-center py-3"><div className="spinner-border spinner-border-sm" /></div>;

  return (
    <form onSubmit={handleSubmit}>
      <h5 className="mb-3">Call Us</h5>
      <div className="row mb-3">
        <div className="col-md-6">
          <label className="form-label">Phone</label>
          <input type="text" className="form-control" value={phone} onChange={(e) => setPhone(e.target.value)} />
        </div>
        <div className="col-md-3">
          <label className="form-label">Email 1</label>
          <input type="email" className="form-control" value={email1} onChange={(e) => setEmail1(e.target.value)} />
        </div>
        <div className="col-md-3">
          <label className="form-label">Email 2</label>
          <input type="email" className="form-control" value={email2} onChange={(e) => setEmail2(e.target.value)} />
        </div>
      </div>

      <h5 className="mb-3">Visit</h5>
      <div className="mb-3">
        <label className="form-label">Address / Location</label>
        <textarea className="form-control" rows={2} value={visitAddress} onChange={(e) => setVisitAddress(e.target.value)} />
      </div>

      <h5 className="mb-3">About Page</h5>
      <div className="mb-3">
        <label className="form-label">Story Image</label>
        
        {(isUploadingImage || isDeletingImage) && (
          <div className="mb-2">
            <div className="spinner-border spinner-border-sm text-primary" role="status">
              <span className="visually-hidden">Processing...</span>
            </div>
            <span className="ms-2 small">{isDeletingImage ? 'Deleting...' : 'Uploading...'}</span>
          </div>
        )}

        {!isUploadingImage && !isDeletingImage && storyImage && (
          <div className="d-flex align-items-center gap-2 mb-2">
            <img src={storyImage} alt="Story Preview" className="img-thumbnail" style={{ maxHeight: '100px' }} />
            <button type="button" className="btn btn-sm btn-outline-danger" onClick={handleDeleteImage}>
              <i className="fas fa-trash"></i> Remove
            </button>
          </div>
        )}

        <input 
          type="file" 
          className="form-control" 
          accept="image/png, image/jpeg, image/jpg, image/webp"
          onChange={handleImageChange} 
          disabled={isUploadingImage || isDeletingImage}
        />
      </div>

      <h5 className="mb-3">Social Links</h5>
      <div className="row mb-3">
        {Object.keys(socialLinks).map((platform) => (
          <div className="col-md-6 mb-2" key={platform}>
            <label className="form-label text-capitalize">{platform}</label>
            <input
              type="url"
              className="form-control"
              value={socialLinks[platform as keyof typeof socialLinks]}
              onChange={(e) => setSocialLinks({ ...socialLinks, [platform]: e.target.value })}
            />
          </div>
        ))}
      </div>

      <button type="submit" className="btn btn-primary" disabled={isSaving || isUploadingImage || isDeletingImage}>
        {isSaving ? 'Saving...' : 'Save Settings'}
      </button>
    </form>
  );
}