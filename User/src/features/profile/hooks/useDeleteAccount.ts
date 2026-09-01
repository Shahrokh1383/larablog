import { useMutation, useQueryClient } from '@tanstack/react-query';
import { profileApi } from '../api/profileApi';
import { useRouter } from 'next/navigation';

export function useDeleteAccount() {
  const queryClient = useQueryClient();
  const router = useRouter();

  return useMutation({
    mutationFn: () => profileApi.deleteAccount(),
    onSuccess: () => {
      queryClient.clear(); // Clear all cache after account deletion
      router.push('/');
    },
  });
}