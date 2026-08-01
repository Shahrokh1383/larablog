import { useAdminAuth } from '@/features/auth';
import { Link } from 'react-router-dom';

export default function DashboardPage() {
  const { user, logout } = useAdminAuth();
  const isAdmin = user?.roles.includes('admin');

  return (
    <div className="container py-5">
      <div className="d-flex justify-content-between align-items-center mb-4">
        <h1>Dashboard</h1>
        <button className="btn btn-outline-danger" onClick={logout}>
          <i className="fas fa-sign-out-alt me-2"></i>Logout
        </button>
      </div>
      <p>Welcome, {user?.name}!</p>
      
      {isAdmin && (
        <div className="mt-4">
          <h5>Admin Tools</h5>
          <Link to="/users" className="btn btn-primary">
            <i className="fas fa-users me-2"></i>Manage Users
          </Link>
        </div>
      )}
    </div>
  );
}