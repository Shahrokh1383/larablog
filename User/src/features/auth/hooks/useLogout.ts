import { useMutation, useQueryClient } from '@tanstack/react-query';
import { useRouter } from 'next/navigation';
import { AxiosError } from 'axios';
import { authApi, authKeys } from '../api/authApi';

export function useLogout() {
  const router = useRouter();
  const queryClient = useQueryClient();

  const mutation = useMutation<void, AxiosError, void>({
    mutationFn: authApi.logout,
    onSuccess: () => {
      queryClient.setQueryData(authKeys.user(), null);
      queryClient.invalidateQueries({ queryKey: authKeys.all });
      router.push('/');
    },
    onError: () => {
      // Failsafe: clear local state even if API fails
      queryClient.setQueryData(authKeys.user(), null);
      router.push('/');
    },
  });

  return {
    logout: mutation.mutate,
    isPending: mutation.isPending,
  };
}