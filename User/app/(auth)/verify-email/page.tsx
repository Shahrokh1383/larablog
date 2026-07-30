'use client';

import { useEffect, useState } from 'react';
import { useRouter, useSearchParams } from 'next/navigation';
import { authApi } from '@/features/auth/api/authApi';
import Link from 'next/link';

export default function VerifyEmailPage() {
  const router = useRouter();
  const searchParams = useSearchParams();
  const [status, setStatus] = useState<'verifying' | 'success' | 'error' | 'waiting'>('waiting');

  useEffect(() => {
    const id = searchParams.get('id');
    const hash = searchParams.get('hash');
    const expires = searchParams.get('expires');
    const signature = searchParams.get('signature');

    // If parameters exist, this is a click from the email
    if (id && hash && expires && signature) {
      setStatus('verifying');
      authApi.verifyEmail(id, hash, { expires, signature })
        .then(() => {
          setStatus('success');
          // Redirect to the main website page after 3 seconds
          setTimeout(() => router.push('/'), 3000);
        })
        .catch(() => setStatus('error'));
    }
  }, [searchParams, router]);

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
          <p className="auth-subtitle">Your email has been successfully verified. Redirecting to the homepage...</p>
        </div>
      </div>
    );
  }

  if (status === 'error') {
    return (
      <div className="auth-form-wrapper active">
        <div className="auth-form-header">
          <h2 className="auth-title">Verification Failed</h2>
          <p className="auth-subtitle">The verification link is invalid or has expired.</p>
          <Link href="/register" className="btn btn-primary-custom btn-auth" style={{ marginTop: '1rem' }}>
            <span>Back to Register</span>
          </Link>
        </div>
      </div>
    );
  }

  // waiting state (user just registered, waiting to click email link)
  return (
    <div className="auth-form-wrapper active">
      <div className="auth-form-header">
        <h2 className="auth-title">Verify Your Email</h2>
        <p className="auth-subtitle">We've sent a verification link to your email address. Please check your inbox to continue.</p>
      </div>
    </div>
  );
}