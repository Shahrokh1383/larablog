import { TeamMember } from '../types/about';

const socialIcons: Record<string, string> = {
  twitter: 'fa-brands fa-x-twitter',
  github: 'fa-brands fa-github',
  linkedin: 'fa-brands fa-linkedin-in',
  instagram: 'fa-brands fa-instagram',
  dribbble: 'fa-brands fa-dribbble',
  youtube: 'fa-brands fa-youtube',
  facebook: 'fa-brands fa-facebook-f',
  website: 'fa-solid fa-globe',
};

export default function TeamMemberCard({ member }: { member: TeamMember }) {
  const user = member.user;
  if (!user) return null;

  const links = user.social_links ? Object.entries(user.social_links) : [];

  return (
    <div className="col-lg-3 col-md-4 col-sm-6">
      <div className="team-card">
        <div className="team-avatar">
          <img 
            src={user.avatar || `https://picsum.photos/seed/${user.id}/300/300`} 
            alt={user.name} 
          />
          {links.length > 0 && (
            <div className="team-social-overlay">
              {links.map(([platform, url]) => {
                if (!url) return null;
                const icon = socialIcons[platform] || 'fa-solid fa-link';
                return (
                  <a key={platform} href={url as string} className="social-link" target="_blank" rel="noopener noreferrer">
                    <i className={icon}></i>
                  </a>
                );
              })}
            </div>
          )}
        </div>
        <div className="team-info">
          <h4 className="team-name">{user.name}</h4>
          <span className="team-role">{user.expertise || user.roles[0]}</span>
        </div>
      </div>
    </div>
  );
}