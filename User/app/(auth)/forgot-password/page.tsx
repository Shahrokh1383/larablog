'use client';

import { useForgotPassword } from '@/features/auth/hooks/useForgotPassword';
import ForgotPasswordForm from '@/features/auth/components/ForgotPasswordForm';

export default function ForgotPasswordPage() {
  const { mutateAsync, isPending, isSuccess, data, error } = useForgotPassword();

  return (
    <ForgotPasswordForm 
      onSubmit={(data) => mutateAsync(data)}
      isLoading={isPending}
      isSuccess={isSuccess}
      successMessage={data?.message}
      error={error?.response?.data?.message}
    />
  );
}