import { User } from '@/features/auth/types/auth';
import { DashboardOverview } from '../api/dashboardApi';

interface DashboardHeaderProps {
  user: User;
  overview: DashboardOverview | undefined;
}

export default function DashboardHeader({ user, overview }: DashboardHeaderProps) {
  const memberSince = new Date(user.created_at).toLocaleDateString('en-US', {
    month: 'short',
    year: 'numeric',
  });

  return (
    <section className="profile-header">
      <div className="profile-cover">
        {/* Cover image is omitted as requested; using CSS gradient background instead */}
      </div>
      <div className="profile-info-wrapper">
        <div className="profile-avatar">
          {user.avatar ? (
            <img src={user.avatar} alt={user.name} />
          ) : (
            <div className="profile-avatar-fallback">
              {user.name.charAt(0).toUpperCase()}
            </div>
          )}
        </div>
        <div className="profile-details">
          <h1 className="profile-name">{user.name}</h1>
          <p className="profile-bio">{user.bio || 'No bio available.'}</p>
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