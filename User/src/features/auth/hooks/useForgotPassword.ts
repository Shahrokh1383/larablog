import { useMutation } from '@tanstack/react-query';
import { authApi } from '../api/authApi';
import type { ForgotPasswordData } from '../types/auth';

export function useForgotPassword() {
  return useMutation({
    mutationFn: (data: ForgotPasswordData) => authApi.forgotPassword(data),
  });
}