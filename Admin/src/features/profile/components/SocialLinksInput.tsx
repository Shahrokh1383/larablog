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
    <div className="space-y-4 border-t pt-4 mt-4">
      <h3 className="text-lg font-medium text-gray-900">Social Media</h3>
      {SOCIAL_PLATFORMS.map((platform) => (
        <div key={platform}>
          <label className="block text-sm font-medium text-gray-700 capitalize">
            {platform}
          </label>
          <input
            type="url"
            value={links[platform] || ''}
            onChange={(e) => handleChange(platform, e.target.value)}
            placeholder={`https://${platform}.com/username`}
            className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
          />
        </div>
      ))}
    </div>
  );
}