'use client';

import { useState, useEffect } from 'react';
import { useSearchParams } from 'next/navigation';
import { useResetPassword } from '@/features/auth/hooks/useResetPassword';

export default function ResetPasswordPage() {
  const searchParams = useSearchParams();
  const token = searchParams.get('token') || '';
  const email = searchParams.get('email') || '';

  const { mutateAsync, isPending, isSuccess, data } = useResetPassword();
  const [password, setPassword] = useState('');
  const [passwordConfirmation, setPasswordConfirmation] = useState('');
  const [showPassword, setShowPassword] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError(null);
    if (password !== passwordConfirmation) {
      setError('Passwords do not match');
      return;
    }
    try {
      await mutateAsync({ token, email, password, password_confirmation: passwordConfirmation });
    } catch (err: any) {
      setError(err.response?.data?.message || 'Reset failed');
    }
  };

  return (
    <div className="auth-form-wrapper active">
      <div className="auth-form-header">
        <a href="/" className="auth-logo">
          <span className="logo-icon"><i className="fa-sharp fa-solid fa-blog"></i></span>
          <span className="logo-text">Lara<span className="logo-accent">Blog</span></span>
        </a>
        <h2 className="auth-title">Reset Password</h2>
        <p className="auth-subtitle">Choose a new password for your account</p>
      </div>
      <form className="auth-form" onSubmit={handleSubmit} noValidate>
        <div className="form-group">
          <label className="form-label">New Password</label>
          <div className="password-input-wrapper">
            <input
              type={showPassword ? 'text' : 'password'}
              className="form-control"
              placeholder="••••••••"
              required
              value={password}
              onChange={(e) => setPassword(e.target.value)}
            />
            <button type="button" className="password-toggle" onClick={() => setShowPassword(!showPassword)}>
              <i className={`fa-sharp fa-solid ${showPassword ? 'fa-eye-slash' : 'fa-eye'}`}></i>
            </button>
          </div>
        </div>
        <div className="form-group">
          <label className="form-label">Confirm Password</label>
          <input
            type="password"
            className="form-control"
            placeholder="••••••••"
            required
            value={passwordConfirmation}
            onChange={(e) => setPasswordConfirmation(e.target.value)}
          />
        </div>
        {error && <div className="alert alert-danger py-1 px-2">{error}</div>}
        {isSuccess && <div className="alert alert-success py-1 px-2">{data?.message}</div>}
        <button type="submit" className="btn btn-primary-custom btn-auth" disabled={isPending}>
          {isPending ? (
            <i className="fa-sharp fa-solid fa-spinner fa-spin"></i>
          ) : (
            <>
              <span>Reset Password</span>
              <i className="fa-sharp fa-solid fa-key"></i>
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