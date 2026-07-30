'use client';

import React, { useState } from 'react';

interface RegisterFormProps {
  onSubmit: (data: {
    name: string;
    email: string;
    password: string;
    password_confirmation: string;
  }) => void;
  isLoading?: boolean;
  error?: string | null;
  errors?: Record<string, string[]>;
}

export default function RegisterForm({ onSubmit, isLoading, error, errors }: RegisterFormProps) {
  const [name, setName] = useState('');
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [passwordConfirmation, setPasswordConfirmation] = useState('');
  const [showPassword, setShowPassword] = useState(false);
  const [showConfirm, setShowConfirm] = useState(false);
  const [agree, setAgree] = useState(false);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    await onSubmit({ name, email, password, password_confirmation: passwordConfirmation });
  };

  return (
    <form className="auth-form" onSubmit={handleSubmit} noValidate>
      <div className="form-group">
        <label className="form-label">Full Name</label>
        <input
          type="text"
          className={`form-control ${errors?.name ? 'is-invalid' : ''}`}
          placeholder="John Doe"
          required
          value={name}
          onChange={(e) => setName(e.target.value)}
        />
        {errors?.name && <div className="invalid-feedback d-block">{errors.name[0]}</div>}
      </div>
      <div className="form-group">
        <label className="form-label">Email</label>
        <input
          type="email"
          className={`form-control ${errors?.email ? 'is-invalid' : ''}`}
          placeholder="you@example.com"
          required
          value={email}
          onChange={(e) => setEmail(e.target.value)}
        />
        {errors?.email && <div className="invalid-feedback d-block">{errors.email[0]}</div>}
      </div>
      <div className="form-group">
        <label className="form-label">Password</label>
        <div className="password-input-wrapper">
          <input
            type={showPassword ? 'text' : 'password'}
            className={`form-control ${errors?.password ? 'is-invalid' : ''}`}
            placeholder="••••••••"
            required
            value={password}
            onChange={(e) => setPassword(e.target.value)}
          />
          <button type="button" className="password-toggle" onClick={() => setShowPassword(!showPassword)}>
            <i className={`fa-sharp fa-solid ${showPassword ? 'fa-eye-slash' : 'fa-eye'}`}></i>
          </button>
        </div>
        {errors?.password && <div className="invalid-feedback d-block">{errors.password[0]}</div>}
      </div>
      <div className="form-group">
        <label className="form-label">Confirm Password</label>
        <div className="password-input-wrapper">
          <input
            type={showConfirm ? 'text' : 'password'}
            className="form-control"
            placeholder="••••••••"
            required
            value={passwordConfirmation}
            onChange={(e) => setPasswordConfirmation(e.target.value)}
          />
          <button type="button" className="password-toggle" onClick={() => setShowConfirm(!showConfirm)}>
            <i className={`fa-sharp fa-solid ${showConfirm ? 'fa-eye-slash' : 'fa-eye'}`}></i>
          </button>
        </div>
      </div>
      <div className="form-group form-options">
        <label className="terms-agree">
          <input type="checkbox" required checked={agree} onChange={(e) => setAgree(e.target.checked)} /> I agree to the{' '}
          <a href="/terms">Terms &amp; Conditions</a>
        </label>
      </div>
      {error && <div className="alert alert-danger py-1 px-2">{error}</div>}
      <button type="submit" className="btn btn-primary-custom btn-auth" disabled={isLoading}>
        {isLoading ? (
          <i className="fa-sharp fa-solid fa-spinner fa-spin"></i>
        ) : (
          <>
            <span>Create Account</span>
            <i className="fa-sharp fa-solid fa-user-plus"></i>
          </>
        )}
      </button>
    </form>
  );
}