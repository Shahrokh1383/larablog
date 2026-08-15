import { useEffect, useState } from 'react';
import { useRouter, useSearchParams } from 'next/navigation';
import { useQueryClient } from '@tanstack/react-query';
import { authApi, authKeys } from '../api/authApi';

export function useOAuthCallback() {
  const router = useRouter();
  const searchParams = useSearchParams();
  const queryClient = useQueryClient();
  const [isProcessing, setIsProcessing] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const authError = searchParams.get('error');
    if (authError) {
      setError(`Authentication failed: ${authError}`);
      setIsProcessing(false);
      return;
    }

    const completeOAuth = async () => {
      try {
        const user = await authApi.getUser();
        queryClient.setQueryData(authKeys.user(), user);
        router.push('/dashboard');
      } catch {
        setError('Failed to complete authentication. Please try again.');
        setIsProcessing(false);
      }
    };

    completeOAuth();
  }, [router, searchParams, queryClient]);

  return { isProcessing, error };
}