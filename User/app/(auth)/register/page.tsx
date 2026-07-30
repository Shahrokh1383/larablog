'use client';

import { useState } from 'react';
import RegisterForm from '@/features/auth/components/RegisterForm';
import SocialButtons from '@/features/auth/components/SocialButtons';
import { useRegister } from '@/features/auth/hooks/useRegister';

export default function RegisterPage() {
  const { register } = useRegister();
  const [error, setError] = useState<string | null>(null);
  const [isLoading, setIsLoading] = useState(false);

  const handleSubmit = async (data: {
    name: string;
    email: string;
    password: string;
    password_confirmation: string;
  }) => {
    setIsLoading(true);
    setError(null);
    try {
      await register(data);
    } catch (err: any) {
      setError(err.response?.data?.message || 'Registration failed');
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <>
      <div className="auth-form-header">
        <a href="/" className="auth-logo">
          <span className="logo-icon"><i className="fa-sharp fa-solid fa-blog"></i></span>
          <span className="logo-text">Lara<span className="logo-accent">Blog</span></span>
        </a>
        <h2 className="auth-title">Create Account</h2>
        <p className="auth-subtitle">Join our community today</p>
      </div>
      <RegisterForm onSubmit={handleSubmit} isLoading={isLoading} error={error} />
      <SocialButtons />
    </>
  );
}