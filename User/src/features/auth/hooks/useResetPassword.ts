import { useMutation } from '@tanstack/react-query';
import { useRouter } from 'next/navigation';
import { AxiosError } from 'axios';
import { authApi } from '../api/authApi';
import type { ResetPasswordData, LaravelValidationError } from '../types/auth';

export function useResetPassword() {
  const router = useRouter();

  return useMutation<{ message: string }, AxiosError<LaravelValidationError>, ResetPasswordData>({
    mutationFn: (data) => authApi.resetPassword(data),
    onSuccess: () => {
      setTimeout(() => router.push('/login'), 2000);
    },
  });
}