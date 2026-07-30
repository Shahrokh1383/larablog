'use client';

import { useState } from 'react';
import LoginForm from '@/features/auth/components/LoginForm';
import SocialButtons from '@/features/auth/components/SocialButtons';
import { useLogin } from '@/features/auth/hooks/useLogin';

export default function LoginPage() {
  const { login } = useLogin();
  const [error, setError] = useState<string | null>(null);
  const [isLoading, setIsLoading] = useState(false);

  const handleSubmit = async (data: { email: string; password: string; remember: boolean }) => {
    setIsLoading(true);
    setError(null);
    try {
      await login(data);
    } catch (err: any) {
      setError(err.response?.data?.message || 'Invalid credentials');
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <>
      <div className="auth-form-header">
        {/* Logo identical to prototype */}
        <a href="/" className="auth-logo">
          <span className="logo-icon"><i className="fa-sharp fa-solid fa-blog"></i></span>
          <span className="logo-text">Lara<span className="logo-accent">Blog</span></span>
        </a>
        <h2 className="auth-title">Welcome Back</h2>
        <p className="auth-subtitle">Sign in to continue your journey</p>
      </div>
      <LoginForm onSubmit={handleSubmit} isLoading={isLoading} error={error} />
      <SocialButtons />
    </>
  );
}