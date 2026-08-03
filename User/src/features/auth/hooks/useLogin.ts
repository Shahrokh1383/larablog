import { useMutation } from '@tanstack/react-query';
import { AxiosError } from 'axios';
import { useAuth } from '../context/AuthContext';
import { useRouter } from 'next/navigation';

interface LaravelValidationError {
  message: string;
  errors?: Record<string, string[]>;
}

export function useLogin() {
  const { login } = useAuth();
  const router = useRouter();

  return useMutation<any, AxiosError<LaravelValidationError>, { email: string; password: string; remember?: boolean }>({
    mutationFn: (credentials) => login(credentials),
    onSuccess: () => {
      router.push('/'); 
    },
  });
}