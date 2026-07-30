'use client';

import { usePathname } from 'next/navigation';
import '@/styles/auth.css';
import { useTheme } from '@/providers/ThemeProvider';
import AuthSlider from '@/features/auth/components/AuthSlider';

export default function AuthLayoutClient({ children }: { children: React.ReactNode }) {
  const pathname = usePathname();
  const { theme, toggleTheme } = useTheme();
  
  // Determine layout state based on route
  const isSignup = pathname.includes('register');
  const isSinglePanel = pathname.includes('forgot') || pathname.includes('reset');

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