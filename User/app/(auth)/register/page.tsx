'use client';

import { useRegister } from '@/features/auth/hooks/useRegister';
import RegisterForm from '@/features/auth/components/RegisterForm';

export default function RegisterPage() {
  const { mutateAsync, isPending, error } = useRegister();

  const validationErrors = error?.response?.data?.errors;
  const genericError = !validationErrors ? error?.response?.data?.message || null : null;

  return (
    <RegisterForm
      onSubmit={(data) => mutateAsync(data)}
      isLoading={isPending}
      error={genericError}
      errors={validationErrors}
    />
  );
}