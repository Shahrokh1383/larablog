import { useMutation } from '@tanstack/react-query';
import { AxiosError } from 'axios';
import { useAuth } from '../context/AuthContext';
import { useRouter } from 'next/navigation';

export function useLogin() {
  const { login } = useAuth();
  const router = useRouter();

  return useMutation<any, AxiosError<{ message: string }>, { email: string; password: string; remember?: boolean }>({
    mutationFn: (credentials) => login(credentials),
    onSuccess: () => {
      router.push('/dashboard');
    },
  });
}