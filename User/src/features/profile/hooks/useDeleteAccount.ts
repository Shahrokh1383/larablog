import { useMutation, useQueryClient } from '@tanstack/react-query';
import { profileApi } from '../api/profileApi';
import { useAuth } from '@/features/auth/context/AuthContext';
import { useRouter } from 'next/navigation';

export function useDeleteAccount() {
  const queryClient = useQueryClient();
  const { setUser } = useAuth();
  const router = useRouter();

  return useMutation({
    mutationFn: () => profileApi.deleteAccount(),
    onSuccess: () => {
      setUser(null);
      queryClient.clear(); // Clear all cache after account deletion
      router.push('/');
    },
  });
}