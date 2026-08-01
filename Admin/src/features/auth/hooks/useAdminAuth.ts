import { useQuery, useQueryClient } from '@tanstack/react-query';
import { authApi } from '../api/authApi';
import { useNavigate } from 'react-router-dom';

const ALLOWED_PANEL_ROLES = ['admin', 'editor', 'author'];

export function useAdminAuth() {
  const queryClient = useQueryClient();
  const navigate = useNavigate();

  const {
    data: user,
    isLoading,
    isError,
  } = useQuery({
    queryKey: ['auth', 'user'],
    queryFn: authApi.getUser,
    enabled: !!localStorage.getItem('auth_token'), // only fetch if token exists
    retry: false,
    staleTime: 5 * 60 * 1000, // 5 min
  });

  const logout = async () => {
    try {
      await authApi.logout();
    } finally {
      localStorage.removeItem('auth_token');
      queryClient.removeQueries({ queryKey: ['auth', 'user'] });
      navigate('/login');
    }
  };

  // Check if user exists AND has at least one of the allowed roles
  const isAuthorized = !!user && user.roles.some(role => ALLOWED_PANEL_ROLES.includes(role));

  return {
    user: user ?? null,
    isLoading,
    isAuthenticated: isAuthorized,
    logout,
  };
}