'use client';

import { useEffect, useState } from 'react';
import { useRouter, useSearchParams } from 'next/navigation';
import { useQueryClient } from '@tanstack/react-query';
import { authApi } from '@/features/auth/api/authApi';

export default function OAuthCallbackPage() {
  const router = useRouter();
  const searchParams = useSearchParams();
  const queryClient = useQueryClient();
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const token = searchParams.get('token');

    if (token) {
      // Fetch user details using the token
      authApi.getUserWithToken(token)
        .then((user) => {
          queryClient.setQueryData(['auth', 'user'], user);
          router.push('/dashboard');
        })
        .catch(() => setError('Authentication failed. Please try again.'));
    } else {
      setError('No authentication token provided.');
    }
  }, [searchParams, router, queryClient]);

  if (error) return <p className="text-center mt-5 text-danger">{error}</p>;
  return <p className="text-center mt-5">Completing authentication...</p>;
}