import { env } from '@/shared/lib/env';
import { endpoints } from '@/shared/api/endpoints';

type OAuthProvider = 'github' | 'google' | 'facebook';

export function useOAuthRedirect() {
  const redirectToProvider = (provider: OAuthProvider) => {
    window.location.href = `${env.apiBaseUrl}${endpoints.auth.oauthRedirect(provider)}`;
  };

  return { redirectToProvider };
}