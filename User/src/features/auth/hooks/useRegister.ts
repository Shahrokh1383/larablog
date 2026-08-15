import { useMutation, useQueryClient } from '@tanstack/react-query';
import { useRouter } from 'next/navigation';
import { AxiosError } from 'axios';
import { authApi, authKeys } from '../api/authApi';
import type { AuthResponse, RegisterCredentials, LaravelValidationError } from '../types/auth';

export function useRegister() {
  const router = useRouter();
  const queryClient = useQueryClient();

  return useMutation<AuthResponse, AxiosError<LaravelValidationError>, RegisterCredentials>({
    mutationFn: (credentials) => authApi.register(credentials),
    onSuccess: (data) => {
      queryClient.setQueryData(authKeys.user(), data.user);
      router.push('/verify-email');
    },
  });
}