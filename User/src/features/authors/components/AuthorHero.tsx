import Link from 'next/link';
import type { Author } from '../types/author';

const SOCIAL_ICONS: Record<string, { icon: string; label: string }> = {
  twitter: { icon: 'fa-brands fa-x-twitter', label: 'Twitter' },
  github: { icon: 'fa-brands fa-github', label: 'GitHub' },
  linkedin: { icon: 'fa-brands fa-linkedin-in', label: 'LinkedIn' },
  instagram: { icon: 'fa-brands fa-instagram', label: 'Instagram' },
  dribbble: { icon: 'fa-brands fa-dribbble', label: 'Dribbble' },
  website: { icon: 'fa-sharp fa-solid fa-globe', label: 'Website' },
};

const formatViews = (views: number) => {
  if (views >= 1000) return (views / 1000).toFixed(0) + 'K';
  return views.toString();
};

interface AuthorHeroProps {
  author: Author;
}

export default function AuthorHero({ author }: AuthorHeroProps) {
  const avatarUrl = author.avatar || `https://picsum.photos/seed/${author.id}/200/200`;

  return (
    <section className="author-profile-hero">
      <div className="hero-bg-shapes">
        <div className="shape shape-1"></div>
        <div className="shape shape-2"></div>
        <div className="shape shape-3"></div>
      </div>
      <div className="container">
        <div className="author-profile-content">
          <Link href="/authors" className="back-to-authors">
            <i className="fa-sharp fa-solid fa-arrow-left"></i> All Authors
          </Link>
          <div className="author-profile-card">
            <div className="author-profile-avatar">
              <img src={avatarUrl} alt={author.name} loading="lazy" />
            </div>
            <div className="author-profile-info">
              <h1 className="author-profile-name">{author.name}</h1>
              <span className="author-profile-role">{author.expertise || 'Author'}</span>
              <p className="author-profile-bio">{author.bio || 'No biography available.'}</p>
              
              <div className="author-profile-stats">
                <div className="profile-stat">
                  <span className="profile-stat-number">{author.posts_count}</span>
                  <span className="profile-stat-label">Articles</span>
                </div>
                <div className="profile-stat">
                  <span className="profile-stat-number">{formatViews(author.total_views)}</span>
                  <span className="profile-stat-label">Total Views</span>
                </div>
                <div className="profile-stat">
                  <span className="profile-stat-number">{author.years_of_experience || 0}</span>
                  <span className="profile-stat-label">Years Writing</span>
                </div>
              </div>
              
              <div className="author-profile-social">
                {author.social_links &&
                  Object.entries(author.social_links).map(([platform, url]) => {
                    const iconData = SOCIAL_ICONS[platform];
                    if (!iconData || !url) return null;
                    return (
                      <a key={platform} href={url} target="_blank" rel="noopener noreferrer" aria-label={iconData.label}>
                        <i className={iconData.icon}></i>
                      </a>
                    );
                  })}
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  );
}