import { useAdminAuth } from '@/features/auth';

export default function DashboardPage() {
  const { user, logout } = useAdminAuth();

  return (
    <div className="container py-5">
      <div className="d-flex justify-content-between align-items-center mb-4">
        <h1>Dashboard</h1>
        <button className="btn btn-outline-danger" onClick={logout}>
          <i className="fas fa-sign-out-alt me-2"></i>Logout
        </button>
      </div>
      <p>Welcome, {user?.name}!</p>
    </div>
  );
}