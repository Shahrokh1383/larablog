'use client';

import Link from 'next/link';
import LoginForm from './LoginForm';
import RegisterForm from './RegisterForm';
import SocialButtons from './SocialButtons';
import { useLogin } from '../hooks/useLogin';
import { useRegister } from '../hooks/useRegister';

export default function AuthSlider() {
  // Hooks own all logic and state (Constitution Article V & VI)
  const loginMutation = useLogin();
  const registerMutation = useRegister();

  return (
    <>
      <div className="auth-forms-panel">
        <div className="auth-forms-slider" id="formsSlider">
          {/* Login Form Wrapper */}
          <div className="auth-form-wrapper" id="loginFormWrapper">
            <div className="auth-form-header">
              <Link href="/" className="auth-logo">
                <span className="logo-icon"><i className="fa-sharp fa-solid fa-blog"></i></span>
                <span className="logo-text">Lara<span className="logo-accent">Blog</span></span>
              </Link>
              <h2 className="auth-title">Welcome Back</h2>
              <p className="auth-subtitle">Sign in to continue your journey</p>
            </div>
            <LoginForm 
              onSubmit={loginMutation.mutate} 
              isLoading={loginMutation.isPending} 
              error={loginMutation.error?.response?.data?.message}
              errors={loginMutation.error?.response?.data?.errors}
            />
            <SocialButtons />
          </div>

          {/* Signup Form Wrapper */}
          <div className="auth-form-wrapper" id="signupFormWrapper">
            <div className="auth-form-header">
              <Link href="/" className="auth-logo">
                <span className="logo-icon"><i className="fa-sharp fa-solid fa-blog"></i></span>
                <span className="logo-text">Lara<span className="logo-accent">Blog</span></span>
              </Link>
              <h2 className="auth-title">Create Account</h2>
              <p className="auth-subtitle">Join our community today</p>
            </div>
            <RegisterForm 
              onSubmit={registerMutation.mutate} 
              isLoading={registerMutation.isPending} 
              error={registerMutation.error?.response?.data?.message}
              errors={registerMutation.error?.response?.data?.errors}
            />
            <SocialButtons />
          </div>
        </div>
      </div>

      <div className="auth-welcome-panel">
        <div className="welcome-slider" id="welcomeSlider">
          <div className="welcome-content welcome-login" id="welcomeLogin">
            <div className="welcome-inner">
              <div className="welcome-icon"><i className="fa-sharp fa-solid fa-user-lock"></i></div>
              <h2 className="welcome-title">Hello, Friend!</h2>
              <p className="welcome-text">Enter your personal details and start your journey with us</p>
              <Link href="/register" className="btn btn-outline-custom btn-switch" id="switchToSignup">
                <span>Sign Up</span>
                <i className="fa-sharp fa-solid fa-arrow-right"></i>
              </Link>
            </div>
          </div>
          <div className="welcome-content welcome-signup" id="welcomeSignup">
            <div className="welcome-inner">
              <div className="welcome-icon"><i className="fa-sharp fa-solid fa-user-plus"></i></div>
              <h2 className="welcome-title">Welcome Back!</h2>
              <p className="welcome-text">To keep connected with us please login with your personal info</p>
              <Link href="/login" className="btn btn-outline-custom btn-switch" id="switchToLogin">
                <span>Sign In</span>
                <i className="fa-sharp fa-solid fa-arrow-right"></i>
              </Link>
            </div>
          </div>
        </div>
      </div>
    </>
  );
}