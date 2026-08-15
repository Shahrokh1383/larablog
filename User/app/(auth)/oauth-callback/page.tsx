'use client';

import { Suspense } from 'react';
import { useOAuthCallback } from '@/features/auth/hooks/useOAuthCallback';
import Link from 'next/link';

function OAuthCallbackContent() {
  const { error } = useOAuthCallback();

  if (error) {
    return (
      <div className="auth-form-wrapper active">
        <div className="auth-form-header">
          <h2 className="auth-title">Authentication Failed</h2>
          <p className="auth-subtitle text-danger">{error}</p>
          <Link href="/login" className="btn btn-primary-custom btn-auth" style={{ marginTop: '1rem' }}>
            <span>Back to Login</span>
          </Link>
        </div>
      </div>
    );
  }

  return (
    <div className="auth-form-wrapper active">
      <div className="auth-form-header">
        <h2 className="auth-title">Completing Authentication...</h2>
        <p className="auth-subtitle">Please wait while we complete your sign-in.</p>
      </div>
    </div>
  );
}

export default function OAuthCallbackPage() {
  return (
    <Suspense fallback={<div className="auth-form-wrapper active">Loading...</div>}>
      <OAuthCallbackContent />
    </Suspense>
  );
}