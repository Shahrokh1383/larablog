import { env } from '@/shared/lib/env';

export function useOAuthRedirect() {
  const redirectToProvider = (provider: 'github' | 'google' | 'facebook') => {
    window.location.href = `${env.apiBaseUrl}/auth/oauth/${provider}/redirect`;
  };

  return { redirectToProvider };
}