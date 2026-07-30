'use client';

import React, { useState } from 'react';

interface LoginFormProps {
  onSubmit: (data: { email: string; password: string; remember: boolean }) => Promise<void>;
  isLoading?: boolean;
  error?: string | null;
}

export default function LoginForm({ onSubmit, isLoading, error }: LoginFormProps) {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [remember, setRemember] = useState(false);
  const [showPassword, setShowPassword] = useState(false);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    await onSubmit({ email, password, remember });
  };

  return (
    <form className="auth-form" onSubmit={handleSubmit} noValidate>
      <div className="form-group">
        <label className="form-label">Email</label>
        <input
          type="email"
          className="form-control"
          placeholder="you@example.com"
          required
          value={email}
          onChange={(e) => setEmail(e.target.value)}
        />
      </div>
      <div className="form-group">
        <label className="form-label">Password</label>
        <div className="password-input-wrapper">
          <input
            type={showPassword ? 'text' : 'password'}
            className="form-control"
            placeholder="••••••••"
            required
            value={password}
            onChange={(e) => setPassword(e.target.value)}
          />
          <button
            type="button"
            className="password-toggle"
            onClick={() => setShowPassword(!showPassword)}
            aria-label="Toggle password visibility"
          >
            <i className={`fa-sharp fa-solid ${showPassword ? 'fa-eye-slash' : 'fa-eye'}`}></i>
          </button>
        </div>
      </div>
      <div className="form-group form-options">
        <label className="remember-me">
          <input
            type="checkbox"
            checked={remember}
            onChange={(e) => setRemember(e.target.checked)}
          />{' '}
          Remember me
        </label>
        <a href="/forgot-password" className="forgot-link">Forgot Password?</a>
      </div>
      {error && <div className="alert alert-danger py-1 px-2">{error}</div>}
      <button type="submit" className="btn btn-primary-custom btn-auth" disabled={isLoading}>
        {isLoading ? (
          <i className="fa-sharp fa-solid fa-spinner fa-spin"></i>
        ) : (
          <>
            <span>Sign In</span>
            <i className="fa-sharp fa-solid fa-arrow-right"></i>
          </>
        )}
      </button>
    </form>
  );
}