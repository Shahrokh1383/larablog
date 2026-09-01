import type { TopCommenter } from '../types/dashboard';

interface TopCommentersSectionProps {
  commenters: TopCommenter[] | undefined;
  isLoading: boolean;
  isError: boolean;
}

export default function TopCommentersSection({ commenters, isLoading, isError }: TopCommentersSectionProps) {
  if (isLoading) {
    return (
      <div className="text-center py-5">
        <div className="spinner-border" role="status" />
      </div>
    );
  }

  if (isError) {
    return <div className="alert alert-danger m-4">Failed to load top commenters.</div>;
  }

  const rows = commenters ?? [];

  return (
    <div className="card shadow-sm h-100">
      <div className="card-header bg-white border-bottom">
        <h5 className="mb-0">Top Commenters</h5>
      </div>
      <div className="card-body p-0">
        <div className="table-responsive">
          <table className="table table-hover mb-0 align-middle">
            <thead className="table-light">
              <tr>
                <th>Name</th>
                <th>Email</th>
                <th className="text-end">Comments</th>
              </tr>
            </thead>
            <tbody>
              {rows.length > 0 ? (
                rows.map((commenter) => (
                  <tr key={commenter.user_id ?? commenter.email ?? 'anonymous'}>
                    <td className="fw-semibold">{commenter.name ?? '—'}</td>
                    <td className="text-muted">{commenter.email ?? '—'}</td>
                    <td className="text-end">
                      <span className="badge bg-primary rounded-pill">{commenter.comments_count}</span>
                    </td>
                  </tr>
                ))
              ) : (
                <tr>
                  <td colSpan={3} className="text-center text-muted py-4">No commenters yet.</td>
                </tr>
              )}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
}