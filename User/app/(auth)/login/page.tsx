'use client';

import { useLogin } from '@/features/auth/hooks/useLogin';
import LoginForm from '@/features/auth/components/LoginForm';

export default function LoginPage() {
  const { mutateAsync, isPending, error } = useLogin();

  const validationErrors = error?.response?.data?.errors;
  const genericError = !validationErrors ? error?.response?.data?.message || null : null;

  return (
    <LoginForm
      onSubmit={(data) => mutateAsync(data)}
      isLoading={isPending}
      error={genericError}
      errors={validationErrors}
    />
  );
}