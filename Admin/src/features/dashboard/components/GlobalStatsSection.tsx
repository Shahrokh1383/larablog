import type { DashboardStats } from '../types/dashboard';

interface GlobalStatsSectionProps {
  stats: DashboardStats | undefined;
  isLoading: boolean;
  isError: boolean;
}

export default function GlobalStatsSection({ stats, isLoading, isError }: GlobalStatsSectionProps) {
  if (isLoading) {
    return (
      <div className="text-center py-5">
        <div className="spinner-border" role="status" />
      </div>
    );
  }

  if (isError || !stats) {
    return <div className="alert alert-danger m-4">Failed to load dashboard statistics.</div>;
  }

  return (
    <>
      {/* Global Stat Cards */}
      <div className="row g-4 mb-5">
        <div className="col-md-4">
          <div className="card shadow-sm border-0">
            <div className="card-body">
              <h6 className="text-muted text-uppercase small mb-2">Total Posts</h6>
              <h2 className="mb-0 fw-bold">{stats.total_posts}</h2>
            </div>
          </div>
        </div>
        <div className="col-md-4">
          <div className="card shadow-sm border-0">
            <div className="card-body">
              <h6 className="text-muted text-uppercase small mb-2">Published</h6>
              <h2 className="mb-0 fw-bold text-success">{stats.published_posts}</h2>
            </div>
          </div>
        </div>
        <div className="col-md-4">
          <div className="card shadow-sm border-0">
            <div className="card-body">
              <h6 className="text-muted text-uppercase small mb-2">Total Views</h6>
              <h2 className="mb-0 fw-bold text-primary">{stats.total_views.toLocaleString()}</h2>
            </div>
          </div>
        </div>
      </div>

      {/* Most Popular Lists */}
      <div className="row g-4">
        <div className="col-lg-6">
          <div className="card shadow-sm h-100">
            <div className="card-header bg-white border-bottom">
              <h5 className="mb-0">Most Popular Categories</h5>
            </div>
            <div className="card-body p-0">
              <table className="table table-hover mb-0 align-middle">
                <thead className="table-light">
                  <tr>
                    <th>Category Name</th>
                    <th className="text-end">Posts Count</th>
                  </tr>
                </thead>
                <tbody>
                  {stats.popular_categories.length > 0 ? (
                    stats.popular_categories.map((cat) => (
                      <tr key={cat.id}>
                        <td className="fw-semibold">{cat.name}</td>
                        <td className="text-end">
                          <span className="badge bg-primary rounded-pill">{cat.posts_count}</span>
                        </td>
                      </tr>
                    ))
                  ) : (
                    <tr>
                      <td colSpan={2} className="text-center text-muted py-4">No categories found.</td>
                    </tr>
                  )}
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <div className="col-lg-6">
          <div className="card shadow-sm h-100">
            <div className="card-header bg-white border-bottom">
              <h5 className="mb-0">Most Popular Tags</h5>
            </div>
            <div className="card-body p-0">
              <table className="table table-hover mb-0 align-middle">
                <thead className="table-light">
                  <tr>
                    <th>Tag Name</th>
                    <th className="text-end">Posts Count</th>
                  </tr>
                </thead>
                <tbody>
                  {stats.popular_tags.length > 0 ? (
                    stats.popular_tags.map((tag) => (
                      <tr key={tag.id}>
                        <td>
                          <span className="badge bg-secondary me-2">#</span>
                          {tag.name}
                        </td>
                        <td className="text-end">
                          <span className="badge bg-primary rounded-pill">{tag.posts_count}</span>
                        </td>
                      </tr>
                    ))
                  ) : (
                    <tr>
                      <td colSpan={2} className="text-center text-muted py-4">No tags found.</td>
                    </tr>
                  )}
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </>
  );
}