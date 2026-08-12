import { TeamMember } from '../types/about';

interface TeamMemberCardProps {
  member: TeamMember;
}

export default function TeamMemberCard({ member }: TeamMemberCardProps) {
  const user = member.user;
  if (!user) return null;

  return (
    <div className="col-lg-3 col-md-4 col-sm-6">
      <div className="team-card">
        <div className="team-avatar">
          <img 
            src={user.avatar || `https://picsum.photos/seed/${user.id}/300/300`} 
            alt={user.name} 
          />
          {user.social_links && (
            <div className="team-social-overlay">
              {user.social_links.twitter && (
                <a href={user.social_links.twitter} className="social-link" target="_blank" rel="noopener noreferrer">
                  <i className="fa-brands fa-x-twitter"></i>
                </a>
              )}
              {user.social_links.github && (
                <a href={user.social_links.github} className="social-link" target="_blank" rel="noopener noreferrer">
                  <i className="fa-brands fa-github"></i>
                </a>
              )}
              {user.social_links.linkedin && (
                <a href={user.social_links.linkedin} className="social-link" target="_blank" rel="noopener noreferrer">
                  <i className="fa-brands fa-linkedin-in"></i>
                </a>
              )}
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