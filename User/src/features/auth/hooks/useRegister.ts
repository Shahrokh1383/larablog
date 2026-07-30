import { useMutation } from '@tanstack/react-query';
import { AxiosError } from 'axios';
import { useAuth } from '../context/AuthContext';
import { useRouter } from 'next/navigation';

export function useRegister() {
  const { register } = useAuth();
  const router = useRouter();

  return useMutation<any, AxiosError<{ message: string }>, { name: string; email: string; password: string; password_confirmation: string }>({
    mutationFn: (credentials) => register(credentials),
    onSuccess: () => {
      router.push('/dashboard');
    },
  });
}