import { useMutation } from '@tanstack/react-query';
import { useRouter } from 'next/navigation';
import { AxiosError } from 'axios';
import { authApi } from '../api/authApi';
import type { AuthResponse, RegisterCredentials, LaravelValidationError } from '../types/auth';

export function useRegister() {
  const router = useRouter();

  return useMutation<AuthResponse, AxiosError<LaravelValidationError>, RegisterCredentials>({
    mutationFn: (credentials) => authApi.register(credentials),
    onSuccess: () => {
      router.replace('/verify-email');
    },
  });
}