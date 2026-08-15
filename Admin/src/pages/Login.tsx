import { useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { useAdminLogin, useAdminAuth } from '@/features/auth';
import LoginForm from '@/features/auth/components/LoginForm';

export default function LoginPage() {
  const { isAuthenticated, isLoading: authLoading } = useAdminAuth();
  const navigate = useNavigate();
  const { mutate: login, isPending, error } = useAdminLogin();

  useEffect(() => {
    if (!authLoading && isAuthenticated) {
      navigate('/dashboard', { replace: true });
    }
  }, [isAuthenticated, authLoading, navigate]);

  // Type-safe error extraction thanks to strict useMutation generics
  const validationErrors = error?.response?.data?.errors;
  const serverError = validationErrors?.email?.[0] || error?.response?.data?.message || null;

  if (authLoading) {
    return (
      <div className="d-flex justify-content-center py-5">
        <div className="spinner-border" />
      </div>
    );
  }

  return (
    <div className="container">
      <div className="row justify-content-center min-vh-100 align-items-center">
        <div className="col-12 col-sm-8 col-md-6 col-lg-4">
          <LoginForm
            onSubmit={(credentials) => login(credentials)}
            isLoading={isPending}
            serverError={serverError}
          />
        </div>
      </div>
    </div>
  );
}