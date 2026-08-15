import { env } from '@/shared/lib/env';
import { endpoints } from '@/shared/api/endpoints';

type OAuthProvider = 'github' | 'google' | 'facebook';

export function useOAuthRedirect() {
  const redirectToProvider = (provider: OAuthProvider) => {
    const baseUrl = env.oauthApiBaseUrl.replace(/\/$/, '');
    window.location.href = `${baseUrl}${endpoints.auth.oauthRedirect(provider)}`;
  };

  return { redirectToProvider };
}