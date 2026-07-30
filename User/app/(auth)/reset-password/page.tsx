'use client';

import { Suspense } from 'react';
import { useSearchParams } from 'next/navigation';
import { useResetPassword } from '@/features/auth/hooks/useResetPassword';
import ResetPasswordForm from '@/features/auth/components/ResetPasswordForm';

function ResetPasswordContent() {
  const searchParams = useSearchParams();
  const token = searchParams.get('token') || '';
  const email = searchParams.get('email') || '';

  const { mutateAsync, isPending, isSuccess, data, error } = useResetPassword();

  const handleSubmit = (formData: { password: string; password_confirmation: string }) => {
    mutateAsync({ token, email, ...formData });
  };

  return (
    <ResetPasswordForm 
      onSubmit={handleSubmit}
      isLoading={isPending}
      isSuccess={isSuccess}
      successMessage={data?.message}
      error={error?.response?.data?.message}
    />
  );
}

export default function ResetPasswordPage() {
  return (
    <Suspense fallback={<div>Loading...</div>}>
      <ResetPasswordContent />
    </Suspense>
  );
}