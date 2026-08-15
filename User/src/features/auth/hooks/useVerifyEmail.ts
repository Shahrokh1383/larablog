import { useEffect, useRef } from 'react';
import { useRouter, useSearchParams } from 'next/navigation';
import { useMutation, useQueryClient } from '@tanstack/react-query';
import { AxiosError } from 'axios';
import { authApi, authKeys } from '../api/authApi';
import type { User } from '../types/auth';

export type VerifyStatus = 'idle' | 'verifying' | 'success' | 'error';

interface VerifyPayload {
  id: string;
  hash: string;
  params: Record<string, string | null>;
}

interface VerifyResponse {
  message: string;
  user?: User;
}

export function useVerifyEmail() {
  const router = useRouter();
  const searchParams = useSearchParams();
  const queryClient = useQueryClient();
  const hasAttempted = useRef(false);

  const mutation = useMutation<VerifyResponse, AxiosError<{ message: string }>, VerifyPayload>({
    mutationFn: ({ id, hash, params }) => authApi.verifyEmail(id, hash, params),
    onSuccess: (data) => {
      if (data.user) {
        queryClient.setQueryData(authKeys.user(), data.user);
      }
      setTimeout(() => router.push('/dashboard'), 2000);
    },
  });

  useEffect(() => {
    const id = searchParams.get('id');
    const hash = searchParams.get('hash');
    const expires = searchParams.get('expires');
    const signature = searchParams.get('signature');

    if (id && hash && expires && signature && !hasAttempted.current) {
      hasAttempted.current = true;
      mutation.mutate({ id, hash, params: { expires, signature } });
    }
  }, [searchParams, mutation]);

  const status: VerifyStatus = mutation.isPending
    ? 'verifying'
    : mutation.isSuccess
    ? 'success'
    : mutation.isError
    ? 'error'
    : 'idle';

  return {
    status,
    errorMessage: mutation.error?.response?.data?.message,
  };
}