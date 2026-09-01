import type { AuthorStats } from '../types/dashboard';

interface AuthorStatsSectionProps {
  stats: AuthorStats | undefined;
  isLoading: boolean;
  isError: boolean;
}

export default function AuthorStatsSection({ stats, isLoading, isError }: AuthorStatsSectionProps) {
  if (isLoading) {
    return (
      <div className="text-center py-5">
        <div className="spinner-border" role="status" />
      </div>
    );
  }

  if (isError || !stats) {
    return <div className="alert alert-danger m-4">Failed to load your statistics.</div>;
  }

  return (
    <div className="row g-4">
      <div className="col-md-6">
        <div className="card shadow-sm border-0">
          <div className="card-body">
            <h6 className="text-muted text-uppercase small mb-2">My Posts</h6>
            <h2 className="mb-0 fw-bold">{stats.posts_count}</h2>
          </div>
        </div>
      </div>
      <div className="col-md-6">
        <div className="card shadow-sm border-0">
          <div className="card-body">
            <h6 className="text-muted text-uppercase small mb-2">My Views</h6>
            <h2 className="mb-0 fw-bold text-primary">{stats.total_views.toLocaleString()}</h2>
          </div>
        </div>
      </div>
    </div>
  );
}