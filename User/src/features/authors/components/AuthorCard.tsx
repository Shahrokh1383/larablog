import Link from 'next/link';
import type { Author } from '../types/author';

const SOCIAL_ICONS: Record<string, { icon: string; label: string }> = {
  twitter: { icon: 'fa-brands fa-x-twitter', label: 'Twitter' },
  github: { icon: 'fa-brands fa-github', label: 'GitHub' },
  linkedin: { icon: 'fa-brands fa-linkedin-in', label: 'LinkedIn' },
  instagram: { icon: 'fa-brands fa-instagram', label: 'Instagram' },
  dribbble: { icon: 'fa-brands fa-dribbble', label: 'Dribbble' },
};

interface AuthorCardProps {
  author: Author;
}

export default function AuthorCard({ author }: AuthorCardProps) {
  const avatarUrl = author.avatar || `https://picsum.photos/seed/${author.id}/150/150`;
  const profileUrl = `/author/${author.username || author.id}`;

  return (
    <div className="col-lg-4 col-md-6 author-card-col">
      <div className="author-card">
        <div className="author-card-avatar">
          <img src={avatarUrl} alt={author.name} loading="lazy" />
        </div>
        <div className="author-card-body">
          <h3 className="author-card-name">
            <Link href={profileUrl}>{author.name}</Link>
          </h3>
          <span className="author-card-role">{author.expertise || 'Author'}</span>
          <p className="author-card-bio">{author.bio || 'No biography available.'}</p>
          
          <div className="author-card-meta">
            <span>
              <i className="fa-sharp fa-solid fa-file-lines"></i> {author.posts_count} articles
            </span>
            <span>
              <i className="fa-sharp fa-solid fa-eye"></i> {author.total_views.toLocaleString()} views
            </span>
          </div>
          
          <div className="author-card-social">
            {author.social_links &&
              Object.entries(author.social_links).map(([platform, url]) => {
                const iconData = SOCIAL_ICONS[platform];
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
          
          <Link href={profileUrl} className="btn btn-outline-custom btn-sm w-100 mt-3">
            View Profile
          </Link>
        </div>
      </div>
    </div>
  );
}