import { useMutation } from '@tanstack/react-query';
import { AxiosError } from 'axios';
import { authApi } from '../api/authApi';
import type { ForgotPasswordData } from '../types/auth';

export function useForgotPassword() {
  return useMutation<{ message: string }, AxiosError<{ message: string }>, ForgotPasswordData>({
    mutationFn: (data) => authApi.forgotPassword(data),
  });
}