'use client';

import { Suspense } from 'react';
import { useVerifyEmail } from '@/features/auth/hooks/useVerifyEmail';
import Link from 'next/link';

function VerifyEmailContent() {
  const { status, errorMessage } = useVerifyEmail();

  if (status === 'verifying') {
    return (
      <div className="auth-form-wrapper active">
        <div className="auth-form-header">
          <h2 className="auth-title">Verifying Email...</h2>
          <p className="auth-subtitle">Please wait while we verify your email address.</p>
        </div>
      </div>
    );
  }

  if (status === 'success') {
    return (
      <div className="auth-form-wrapper active">
        <div className="auth-form-header">
          <h2 className="auth-title">Email Verified!</h2>
          <p className="auth-subtitle">Redirecting to your dashboard...</p>
        </div>
      </div>
    );
  }

  if (status === 'error') {
    return (
      <div className="auth-form-wrapper active">
        <div className="auth-form-header">
          <h2 className="auth-title">Verification Failed</h2>
          <p className="auth-subtitle">{errorMessage || 'The verification link is invalid or has expired.'}</p>
          <Link href="/register" className="btn btn-primary-custom btn-auth" style={{ marginTop: '1rem' }}>
            <span>Back to Register</span>
          </Link>
        </div>
      </div>
    );
  }

  return (
    <div className="auth-form-wrapper active">
      <div className="auth-form-header">
        <h2 className="auth-title">Verify Your Email</h2>
        <p className="auth-subtitle">We've sent a verification link to your email. Please check your inbox.</p>
      </div>
    </div>
  );
}

export default function VerifyEmailPage() {
  return (
    <Suspense fallback={<div className="auth-form-wrapper active">Loading...</div>}>
      <VerifyEmailContent />
    </Suspense>
  );
}