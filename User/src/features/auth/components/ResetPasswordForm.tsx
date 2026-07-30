'use client';

import { useState } from 'react';
import Link from 'next/link';

interface ResetPasswordFormProps {
  onSubmit: (data: { password: string; password_confirmation: string }) => void;
  isLoading: boolean;
  isSuccess: boolean;
  successMessage?: string;
  error?: string;
}

export default function ResetPasswordForm({ onSubmit, isLoading, isSuccess, successMessage, error }: ResetPasswordFormProps) {
  const [password, setPassword] = useState('');
  const [passwordConfirmation, setPasswordConfirmation] = useState('');
  const [showPassword, setShowPassword] = useState(false);
  const [validationError, setValidationError] = useState<string | null>(null);

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (password !== passwordConfirmation) {
      setValidationError('Passwords do not match');
      return;
    }
    setValidationError(null);
    onSubmit({ password, password_confirmation: passwordConfirmation });
  };

  return (
    <>
      <div className="auth-form-header">
        <Link href="/" className="auth-logo">
          <span className="logo-icon"><i className="fa-sharp fa-solid fa-blog"></i></span>
          <span className="logo-text">Lara<span className="logo-accent">Blog</span></span>
        </Link>
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
        {validationError && <div className="alert alert-danger py-1 px-2">{validationError}</div>}
        {error && <div className="alert alert-danger py-1 px-2">{error}</div>}
        {isSuccess && <div className="alert alert-success py-1 px-2">{successMessage}</div>}
        <button type="submit" className="btn btn-primary-custom btn-auth" disabled={isLoading}>
          {isLoading ? (
            <i className="fa-sharp fa-solid fa-spinner fa-spin"></i>
          ) : (
            <>
              <span>Reset Password</span>
              <i className="fa-sharp fa-solid fa-key"></i>
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