import { useAdminAuth } from '@/features/auth';
import {
  AuthorStatsSection,
  GlobalStatsSection,
  TopCommentersSection,
  useAuthorStats,
  useDashboardStats,
  useTopCommenters,
} from '@/features/dashboard';

export default function DashboardPage() {
  const { user, isLoading: isAuthLoading } = useAdminAuth();

  const roles = user?.roles ?? [];
  const showGlobalStats = roles.includes('admin') || roles.includes('editor');
  const showMyStats = roles.includes('author');
  const showTopCommenters = showGlobalStats;

  const globalStats = useDashboardStats({ enabled: showGlobalStats });
  const authorStats = useAuthorStats({ enabled: showMyStats });
  const topCommenters = useTopCommenters({ enabled: showTopCommenters });

  if (isAuthLoading) {
    return (
      <div className="d-flex justify-content-center py-5">
        <div className="spinner-border" role="status" />
      </div>
    );
  }

  if (!user) {
    return <div className="alert alert-danger m-4">Failed to load dashboard.</div>;
  }

  return (
    <div className="container py-4">
      <h1 className="mb-4">Dashboard Overview</h1>

      <div className="d-flex flex-column gap-5">
        {showMyStats && (
          <section aria-label="Your statistics">
            <AuthorStatsSection
              stats={authorStats.data}
              isLoading={authorStats.isLoading}
              isError={authorStats.isError}
            />
          </section>
        )}

        {showGlobalStats && (
          <section aria-label="Global statistics">
            <GlobalStatsSection
              stats={globalStats.data}
              isLoading={globalStats.isLoading}
              isError={globalStats.isError}
            />
          </section>
        )}

        {showTopCommenters && (
          <section aria-label="Top commenters">
            <TopCommentersSection
              commenters={topCommenters.data}
              isLoading={topCommenters.isLoading}
              isError={topCommenters.isError}
            />
          </section>
        )}
      </div>
    </div>
  );
}