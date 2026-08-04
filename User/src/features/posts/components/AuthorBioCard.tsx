import Link from 'next/link';
import { Author } from '../types/post';

interface AuthorBioCardProps {
  author: Author;
}

const SOCIAL_ICONS: Record<string, { icon: string; label: string }> = {
  twitter:   { icon: 'fa-brands fa-x-twitter',   label: 'Twitter' },
  github:    { icon: 'fa-brands fa-github',      label: 'GitHub' },
  linkedin:  { icon: 'fa-brands fa-linkedin-in', label: 'LinkedIn' },
  instagram: { icon: 'fa-brands fa-instagram',   label: 'Instagram' },
  dribbble:  { icon: 'fa-brands fa-dribbble',    label: 'Dribbble' },
};

export default function AuthorBioCard({ author }: AuthorBioCardProps) {
  return (
    <div className="author-bio-card">
      <img 
        src={author.avatar || `https://picsum.photos/seed/${author.id}/80/80`} 
        alt={author.name} 
        className="author-bio-avatar"
      />
      <div className="author-bio-content">
        <h4>
          <Link href={`/author/${author.username || author.id}`}>
            {author.name}
          </Link>
        </h4>
        <p>{author.bio || 'Senior full-stack developer and open-source contributor.'}</p>
        
        {/* Dynamically render social links if they exist */}
        {author.social_links && Object.keys(author.social_links).length > 0 && (
          <div className="author-social">
            {Object.entries(author.social_links).map(([platform, url]) => {
              const iconData = SOCIAL_ICONS[platform];
              // Guard clause: skip if platform is unsupported or URL is empty
              if (!iconData || !url) return null; 
              
              return (
                <a 
                  key={platform} 
                  href={url} 
                  target="_blank" 
                  rel="noopener noreferrer" 
                  aria-label={iconData.label}
                >
                  <i className={iconData.icon}></i>
                </a>
              );
            })}
          </div>
        )}
      </div>
    </div>
  );
}