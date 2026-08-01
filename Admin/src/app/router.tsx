import { Routes, Route, Navigate } from 'react-router-dom';
import { RequireAuth } from '@/shared/components/RequireAuth';
import { RequireAdmin } from '@/shared/components/RequireAdmin';
import AdminLayout from '@/shared/components/AdminLayout';
import LoginPage from '@/pages/Login';
import DashboardPage from '@/pages/Dashboard';
import UsersPage from '@/pages/Users';

export default function AppRouter() {
  return (
    <Routes>
      <Route path="/login" element={<LoginPage />} />
      
      {/* All authenticated routes share the AdminLayout */}
      <Route
        element={
          <RequireAuth>
            <AdminLayout />
          </RequireAuth>
        }
      >
        <Route path="/dashboard" element={<DashboardPage />} />
        
        {/* Admin-only routes also use the layout, but wrapped with RequireAdmin */}
        <Route
          path="/users"
          element={
            <RequireAdmin>
              <UsersPage />
            </RequireAdmin>
          }
        />
      </Route>
      
      <Route path="*" element={<Navigate to="/dashboard" replace />} />
    </Routes>
  );
}