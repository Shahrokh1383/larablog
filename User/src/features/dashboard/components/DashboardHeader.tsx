import { User } from '@/features/auth/types/auth';
import { DashboardOverview } from '../api/dashboardApi';

interface DashboardHeaderProps {
  user: User;
  overview: DashboardOverview | undefined;
  avatar?: string | null;
  bio?: string | null;
}

export default function DashboardHeader({ user, overview, avatar, bio }: DashboardHeaderProps) {
  const memberSince = new Date(user.created_at).toLocaleDateString('en-US', {
    month: 'short',
    year: 'numeric',
  });

  const resolvedAvatar = avatar ?? user.avatar;
  const resolvedBio = bio ?? user.bio;

  return (
    <section className="profile-header">
      <div className="profile-cover">
      </div>
      <div className="profile-info-wrapper">
        <div className="profile-avatar">
          {resolvedAvatar ? (
            <img src={resolvedAvatar} alt={user.name} />
          ) : (
            <div className="profile-avatar-fallback">
              {user?.name?.charAt(0).toUpperCase() || 'U'}
            </div>
          )}
        </div>
        <div className="profile-details">
          <h1 className="profile-name">{user.name}</h1>
          <p className="profile-bio">{resolvedBio || 'No bio available.'}</p>
          <div className="profile-stats">
            <div className="profile-stat">
              <span className="stat-value">{overview?.total_comments ?? 0}</span>
              <span className="stat-label">Comments</span>
            </div>
            <div className="profile-stat">
              <span className="stat-value">{overview?.total_saved_posts ?? 0}</span>
              <span className="stat-label">Saved Posts</span>
            </div>
            <div className="profile-stat">
              <span className="stat-value">{memberSince}</span>
              <span className="stat-label">Member Since</span>
            </div>
          </div>
        </div>
      </div>
    </section>
  );
}