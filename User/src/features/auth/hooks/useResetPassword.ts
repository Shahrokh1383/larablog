import { useMutation } from '@tanstack/react-query';
import { AxiosError } from 'axios';
import { authApi } from '../api/authApi';
import type { ResetPasswordData } from '../types/auth';

export function useResetPassword() {
  return useMutation<{ message: string }, AxiosError<{ message: string }>, ResetPasswordData>({
    mutationFn: (data) => authApi.resetPassword(data),
  });
}