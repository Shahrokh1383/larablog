import { useMutation } from '@tanstack/react-query';
import { AxiosError } from 'axios';
import { authApi } from '../api/authApi';
import type { ForgotPasswordData, LaravelValidationError } from '../types/auth';

export function useForgotPassword() {
  return useMutation<{ message: string }, AxiosError<LaravelValidationError>, ForgotPasswordData>({
    mutationFn: (data) => authApi.forgotPassword(data),
  });
}