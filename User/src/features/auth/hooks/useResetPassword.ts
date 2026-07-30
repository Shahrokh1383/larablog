import { useMutation } from '@tanstack/react-query';
import { authApi } from '../api/authApi';
import type { ResetPasswordData } from '../types/auth';

export function useResetPassword() {
  return useMutation({
    mutationFn: (data: ResetPasswordData) => authApi.resetPassword(data),
  });
}