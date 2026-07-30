import '@/styles/auth.css'; // ensure auth styles loaded
import type { Metadata } from 'next';

export const metadata: Metadata = {
  title: 'Authentication - LaraBlog',
};

export default function AuthLayout({ children }: { children: React.ReactNode }) {
  return (
    <div className="auth-body">
      <div className="auth-container" id="authContainer">
        <div className="auth-theme-toggle">
          <button className="btn-icon theme-toggle" id="themeToggle" aria-label="Toggle theme">
            <i id="themeIcon" className="fa-sharp fa-solid fa-moon"></i>
          </button>
        </div>
        <div className="auth-forms-panel">
          <div className="auth-forms-slider" id="formsSlider">
            <div className="auth-form-wrapper" id="loginFormWrapper">
              {children}
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
                <a href="/register" className="btn btn-outline-custom btn-switch">
                  <span>Sign Up</span>
                  <i className="fa-sharp fa-solid fa-arrow-right"></i>
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}