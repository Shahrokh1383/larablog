'use client';

import { useState } from 'react';
import Link from 'next/link';

interface ForgotPasswordFormProps {
  onSubmit: (data: { email: string }) => void;
  isLoading: boolean;
  isSuccess: boolean;
  successMessage?: string;
  error?: string;
}

export default function ForgotPasswordForm({ onSubmit, isLoading, isSuccess, successMessage, error }: ForgotPasswordFormProps) {
  const [email, setEmail] = useState('');

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    onSubmit({ email });
  };

  return (
    <>
      <div className="auth-form-header">
        <Link href="/" className="auth-logo">
          <span className="logo-icon"><i className="fa-sharp fa-solid fa-blog"></i></span>
          <span className="logo-text">Lara<span className="logo-accent">Blog</span></span>
        </Link>
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
        {error && <div className="alert alert-danger py-1 px-2">{error}</div>}
        {isSuccess && (
          <div className="alert alert-success py-1 px-2">{successMessage || 'Reset link sent!'}</div>
        )}
        <button type="submit" className="btn btn-primary-custom btn-auth" disabled={isLoading}>
          {isLoading ? (
            <i className="fa-sharp fa-solid fa-spinner fa-spin"></i>
          ) : (
            <>
              <span>Send Reset Link</span>
              <i className="fa-sharp fa-solid fa-paper-plane"></i>
            </>
          )}
        </button>
        <div style={{ textAlign: 'center', marginTop: '1rem' }}>
          <Link href="/login" className="forgot-link">← Back to Login</Link>
        </div>
      </form>
    </>
  );
}