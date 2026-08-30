'use client';

import { usePathname, useRouter } from 'next/navigation';
import { useEffect } from 'react';
import '@/styles/auth.css';
import { useTheme } from '@/providers/ThemeProvider';
import AuthSlider from '@/features/auth/components/AuthSlider';
import { useAuth } from '@/features/auth/context/AuthContext';

export default function AuthLayoutClient({ children }: { children: React.ReactNode }) {
  const pathname = usePathname();
  const router = useRouter();
  const { theme, toggleTheme } = useTheme();
  const { isAuthenticated, isLoading } = useAuth();

  // Redirect to dashboard if already authenticated
  useEffect(() => {
    if (!isLoading && isAuthenticated && !pathname.includes('verify-email')) {
      router.replace('/dashboard');
    }
  }, [isLoading, isAuthenticated, router, pathname]);

  // Show a loading spinner while checking auth state or redirecting
  if (isLoading || isAuthenticated) {
    return (
      <div className="d-flex justify-content-center align-items-center" style={{ minHeight: '100vh' }}>
        <div className="spinner-border text-primary" role="status">
          <span className="visually-hidden">Loading...</span>
        </div>
      </div>
    );
  }

  // Determine layout state based on route
  const isSignup = pathname.includes('register');
  const isSinglePanel = pathname.includes('forgot') || pathname.includes('reset') || pathname.includes('verify-email');

  return (
    <div className="auth-body">
      <div 
        className={`auth-container ${isSignup ? 'signup-active' : ''} ${isSinglePanel ? 'single-panel' : ''}`} 
        id="authContainer"
      >
        <div className="auth-theme-toggle">
          <button 
            className="btn-icon theme-toggle" 
            id="themeToggle" 
            aria-label="Toggle theme"
            onClick={toggleTheme}
          >
            <i id="themeIcon" className={`fa-sharp fa-solid ${theme === 'light' ? 'fa-moon' : 'fa-sun'}`}></i>
          </button>
        </div>
        
        {isSinglePanel ? (
          <div className="auth-forms-panel">
            <div className="auth-forms-slider">
              <div className="auth-form-wrapper active">
                {children}
              </div>
            </div>
          </div>
        ) : (
          <AuthSlider /> 
        )}
      </div>
    </div>
  );
}