'use client';

import { useState } from 'react';
import { useForgotPassword } from '@/features/auth/hooks/useForgotPassword';

export default function ForgotPasswordPage() {
  const { mutateAsync, isPending, isSuccess, data } = useForgotPassword();
  const [email, setEmail] = useState('');

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    await mutateAsync({ email });
  };

  return (
    <div className="auth-form-wrapper active">
      <div className="auth-form-header">
        <a href="/" className="auth-logo">
          <span className="logo-icon"><i className="fa-sharp fa-solid fa-blog"></i></span>
          <span className="logo-text">Lara<span className="logo-accent">Blog</span></span>
        </a>
        <h2 className="auth-title">Forgot Password</h2>
        <p className="auth-subtitle">Enter your email and we&apos;ll send you a reset link</p>
      </div>
      <form className="auth-form" onSubmit={handleSubmit} noValidate>
        <div className="form-group">
          <label className="form-label">Email Address</label>
          <input
            type="email"
            className="form-control"
            placeholder="you@example.com"
            required
            value={email}
            onChange={(e) => setEmail(e.target.value)}
          />
        </div>
        {isSuccess && (
          <div className="alert alert-success py-1 px-2">{data?.message || 'Reset link sent!'}</div>
        )}
        <button type="submit" className="btn btn-primary-custom btn-auth" disabled={isPending}>
          {isPending ? (
            <i className="fa-sharp fa-solid fa-spinner fa-spin"></i>
          ) : (
            <>
              <span>Send Reset Link</span>
              <i className="fa-sharp fa-solid fa-paper-plane"></i>
            </>
          )}
        </button>
        <div style={{ textAlign: 'center', marginTop: '1rem' }}>
          <a href="/login" className="forgot-link">← Back to Login</a>
        </div>
      </form>
    </div>
  );
}