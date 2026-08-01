import { Routes, Route, Navigate } from 'react-router-dom';
import { RequireAuth } from '@/shared/components/RequireAuth';
import { RequireAdmin } from '@/shared/components/RequireAdmin';
import LoginPage from '@/pages/Login';
import DashboardPage from '@/pages/Dashboard';
import UsersPage from '@/pages/Users';

export default function AppRouter() {
  return (
    <Routes>
      <Route path="/login" element={<LoginPage />} />
      <Route
        path="/dashboard"
        element={
          <RequireAuth>
            <DashboardPage />
          </RequireAuth>
        }
      />
      <Route
        path="/users"
        element={
          <RequireAdmin>
            <UsersPage />
          </RequireAdmin>
        }
      />
      <Route path="*" element={<Navigate to="/dashboard" replace />} />
    </Routes>
  );
}