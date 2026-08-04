import { SocialLinks } from '../types/profile';

interface SocialLinksInputProps {
  links: SocialLinks;
  onChange: (links: SocialLinks) => void;
}

const SOCIAL_PLATFORMS: (keyof SocialLinks)[] = [
  'twitter', 'github', 'linkedin', 'instagram', 'dribbble'
];

export function SocialLinksInput({ links, onChange }: SocialLinksInputProps) {
  const handleChange = (platform: keyof SocialLinks, value: string) => {
    onChange({ ...links, [platform]: value || undefined });
  };

  return (
    <div className="border-top pt-4 mt-4">
      <h5 className="mb-3">Social Media Links</h5>
      <div className="row">
        {SOCIAL_PLATFORMS.map((platform) => (
          <div className="col-md-6 mb-3" key={platform}>
            <label className="form-label text-capitalize">{platform}</label>
            <input
              type="url"
              className="form-control"
              value={links[platform] || ''}
              onChange={(e) => handleChange(platform, e.target.value)}
              placeholder={`https://${platform}.com/username`}
            />
          </div>
        ))}
      </div>
    </div>
  );
}