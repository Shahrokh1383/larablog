import { useMutation, useQueryClient } from '@tanstack/react-query';
import { useRouter } from 'next/navigation';
import { AxiosError } from 'axios';
import { authApi, authKeys } from '../api/authApi';
import type { AuthResponse, LoginCredentials, LaravelValidationError } from '../types/auth';

export function useLogin() {
  const router = useRouter();
  const queryClient = useQueryClient();

  return useMutation<AuthResponse, AxiosError<LaravelValidationError>, LoginCredentials>({
    mutationFn: (credentials) => authApi.login(credentials),
    onSuccess: (data) => {
      queryClient.setQueryData(authKeys.user(), data.user);
      router.push('/');
    },
  });
}