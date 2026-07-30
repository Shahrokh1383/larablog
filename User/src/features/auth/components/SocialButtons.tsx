'use client';

import { useOAuthRedirect } from '../hooks/useOAuthRedirect';

export default function SocialButtons() {
  const { redirectToProvider } = useOAuthRedirect();

  return (
    <div className="social-login">
      <p className="social-login-text">Or continue with</p>
      <div className="social-buttons">
        <button
          type="button"
          className="social-btn google"
          onClick={() => redirectToProvider('google')}
        >
          <i className="fa-brands fa-google"></i>
        </button>
        <button
          type="button"
          className="social-btn facebook"
          onClick={() => redirectToProvider('facebook')}
        >
          <i className="fa-brands fa-facebook-f"></i>
        </button>
        <button
          type="button"
          className="social-btn github"
          onClick={() => redirectToProvider('github')}
        >
          <i className="fa-brands fa-github"></i>
        </button>
      </div>
    </div>
  );
}