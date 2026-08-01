import { useAdminAuth } from '@/features/auth/hooks/useAdminAuth';
import { Navigate } from 'react-router-dom';

export function RequireAdmin({ children }: { children: React.ReactNode }) {
  const { user, isAuthenticated, isLoading } = useAdminAuth();

  if (isLoading) {
    return (
      <div className="d-flex justify-content-center py-5">
        <div className="spinner-border" />
      </div>
    );
  }

  if (!isAuthenticated) {
    return <Navigate to="/login" replace />;
  }

  if (!user?.roles.includes('admin')) {
    return <Navigate to="/dashboard" replace />;
  }

  return <>{children}</>;
}