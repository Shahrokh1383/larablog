import { useMutation, useQueryClient } from '@tanstack/react-query';
import { AxiosError } from 'axios';
import { authApi } from '../api/authApi';
import type { LoginCredentials, LoginResponse, LaravelValidationError } from '../types/auth';

export function useAdminLogin() {
  const queryClient = useQueryClient();

  return useMutation<LoginResponse, AxiosError<LaravelValidationError>, LoginCredentials>({
    mutationFn: (credentials) => authApi.login(credentials),
    onSuccess: (data) => {
      localStorage.setItem('auth_token', data.token);
      queryClient.setQueryData(['auth', 'user'], data.user);
    },
  });
}