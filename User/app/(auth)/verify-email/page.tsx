'use client';

import { useEffect, useState } from 'react';
import { useRouter, useSearchParams } from 'next/navigation';
import { authApi } from '@/features/auth/api/authApi';
import { useAuth } from '@/features/auth/context/AuthContext';
import Link from 'next/link';

export default function VerifyEmailPage() {
  const router = useRouter();
  const searchParams = useSearchParams();
  const [status, setStatus] = useState<'verifying' | 'success' | 'error' | 'waiting'>('waiting');
  const { setUser } = useAuth();

  useEffect(() => {
    const id = searchParams.get('id');
    const hash = searchParams.get('hash');
    const expires = searchParams.get('expires');
    const signature = searchParams.get('signature');

    if (id && hash && expires && signature) {
      setStatus('verifying');
      authApi.verifyEmail(id, hash, { expires, signature })
        .then((data) => {
          setStatus('success');
          if (data.user) {
            setUser(data.user);
          }
          // Redirect to dashboard (standard post-verification destination)
          setTimeout(() => router.push('/dashboard'), 3000);
        })
        .catch(() => setStatus('error'));
    }
  }, [searchParams, router, setUser]);

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
          <p className="auth-subtitle">Your email has been successfully verified. Redirecting to your dashboard...</p>
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

  // waiting state (just registered, no link clicked yet)
  return (
    <div className="auth-form-wrapper active">
      <div className="auth-form-header">
        <h2 className="auth-title">Verify Your Email</h2>
        <p className="auth-subtitle">We've sent a verification link to your email address. Please check your inbox to continue.</p>
      </div>
    </div>
  );
}