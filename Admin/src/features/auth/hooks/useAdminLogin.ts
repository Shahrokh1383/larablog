import { useMutation, useQueryClient } from '@tanstack/react-query';
import { authApi } from '../api/authApi';
import type { LoginCredentials } from '../types/auth';

export function useAdminLogin() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (credentials: LoginCredentials) => authApi.login(credentials),
    onSuccess: (data) => {
      // Store token
      localStorage.setItem('auth_token', data.token);
      // Pre-populate user query data so useAdminAuth picks it up
      queryClient.setQueryData(['auth', 'user'], data.user);
    },
  });
}