import { useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { useQueryClient } from '@tanstack/react-query';
import AppProviders from './providers';
import AppRouter from './router';

function AuthWatcher() {
  const navigate = useNavigate();
  const queryClient = useQueryClient();

  useEffect(() => {
    const handleUnauthorized = () => {
      // Token is already removed by httpClient interceptor
      queryClient.removeQueries({ queryKey: ['auth', 'user'] });
      navigate('/login', { replace: true });
    };

    window.addEventListener('auth:unauthorized', handleUnauthorized);
    return () => window.removeEventListener('auth:unauthorized', handleUnauthorized);
  }, [navigate, queryClient]);

  return null;
}

export default function App() {
  return (
    <AppProviders>
      <AuthWatcher />
      <AppRouter />
    </AppProviders>
  );
}