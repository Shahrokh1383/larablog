import { useNavigate } from 'react-router-dom';
import { useQueryClient } from '@tanstack/react-query';
import { authApi } from '../api/authApi';

export function useLogout() {
  const navigate = useNavigate();
  const queryClient = useQueryClient();

  const logout = async () => {
    try {
      await authApi.logout();
    } catch (error) {
      // Ignore errors during logout, we still want to clear local state
      console.error('Logout API call failed, but continuing cleanup.', error);
    } finally {
      localStorage.removeItem('auth_token');
      queryClient.removeQueries({ queryKey: ['auth', 'user'] });
      navigate('/login', { replace: true });
    }
  };

  return { logout };
}