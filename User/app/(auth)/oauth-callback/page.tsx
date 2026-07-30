'use client';

import { useEffect, useState } from 'react';
import { useSearchParams } from 'next/navigation';
import { useAuth } from '@/features/auth/context/AuthContext';
import httpClient from '@/shared/api/httpClient';
import { endpoints } from '@/shared/api/endpoints';

export default function OAuthCallbackPage() {
  const searchParams = useSearchParams();
  const { setUser } = useAuth();
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const provider = searchParams.get('provider');
    const code = searchParams.get('code');
    const state = searchParams.get('state');

    if (provider && code) {
      httpClient
        .get(`${endpoints.auth.oauthCallback(provider)}?code=${code}&state=${state}`)
        .then((res) => {
          setUser(res.data.user);
          window.location.href = '/dashboard';
        })
        .catch(() => setError('OAuth failed'));
    } else {
      // If token was passed directly in URL (alternative flow)
      const token = searchParams.get('token');
      if (token) {
        // store token in localStorage? Not recommended for Sanctum cookie.
        // Better: backend redirects with token, we store it in cookie via /login response.
        // For now, navigate to dashboard; user will be fetched.
        window.location.href = '/dashboard';
      }
    }
  }, [searchParams, setUser]);

  if (error) return <p className="text-center mt-5">{error}</p>;
  return <p className="text-center mt-5">Completing authentication...</p>;
}