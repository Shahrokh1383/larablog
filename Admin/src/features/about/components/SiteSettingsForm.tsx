import { useState, useEffect } from 'react';
import type { SiteSettings } from '../types/about';

interface Props {
  settings: SiteSettings | undefined;
  isLoading: boolean;
  isSaving: boolean;
  onSubmit: (data: Partial<SiteSettings>) => void;
}

export default function SiteSettingsForm({ settings, isLoading, isSaving, onSubmit }: Props) {
  const [phone, setPhone] = useState('');
  const [email1, setEmail1] = useState('');
  const [email2, setEmail2] = useState('');
  const [visitAddress, setVisitAddress] = useState('');
  const [storyImage, setStoryImage] = useState('');
  const [socialLinks, setSocialLinks] = useState({
    linkedin: '',
    github: '',
    twitter: '',
    instagram: '',
    dribbble: '',
    youtube: '',
  });

  useEffect(() => {
    if (settings) {
      setPhone(settings.call_us_phone || '');
      setEmail1(settings.call_us_emails?.[0] || '');
      setEmail2(settings.call_us_emails?.[1] || '');
      setVisitAddress(settings.visit_address || '');
      setStoryImage(settings.story_image || '');
      setSocialLinks({
        linkedin: settings.social_links?.linkedin || '',
        github: settings.social_links?.github || '',
        twitter: settings.social_links?.twitter || '',
        instagram: settings.social_links?.instagram || '',
        dribbble: settings.social_links?.dribbble || '',
        youtube: settings.social_links?.youtube || '',
      });
    }
  }, [settings]);

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    const call_us_emails = [email1, email2].filter(Boolean);
    onSubmit({
      call_us_phone: phone || null,
      call_us_emails,
      visit_address: visitAddress || null,
      story_image: storyImage || null,
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

      {/* Added Story Image Input */}
      <h5 className="mb-3">About Page</h5>
      <div className="mb-3">
        <label className="form-label">Story Image URL</label>
        <input type="url" className="form-control" value={storyImage} onChange={(e) => setStoryImage(e.target.value)} />
        <small className="text-muted">If empty, a default placeholder will be used.</small>
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

      <button type="submit" className="btn btn-primary" disabled={isSaving}>
        {isSaving ? 'Saving...' : 'Save Settings'}
      </button>
    </form>
  );
}