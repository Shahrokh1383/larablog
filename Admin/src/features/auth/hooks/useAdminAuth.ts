import { useQuery } from '@tanstack/react-query';
import { authApi } from '../api/authApi';

const ALLOWED_PANEL_ROLES = ['admin', 'editor', 'author'];

export function useAdminAuth() {
  const {
    data: user,
    isLoading,
    isError,
  } = useQuery({
    queryKey: ['auth', 'user'],
    queryFn: authApi.getUser,
    enabled: !!localStorage.getItem('auth_token'),
    retry: false,
    staleTime: 5 * 60 * 1000,
  });

  const isAuthorized = !!user && user.roles.some(role => ALLOWED_PANEL_ROLES.includes(role));

  return {
    user: user ?? null,
    isLoading,
    isAuthenticated: isAuthorized,
    isError,
  };
}