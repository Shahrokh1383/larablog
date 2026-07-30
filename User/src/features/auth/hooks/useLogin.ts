import { useAuth } from '../context/AuthContext';
import { useRouter } from 'next/navigation';

export function useLogin() {
  const { login } = useAuth();
  const router = useRouter();
  // const { showToast } = useToast();

  const loginUser = async (credentials: { email: string; password: string; remember?: boolean }) => {
    try {
      await login(credentials);
      router.push('/dashboard');
    } catch (error: any) {
      throw error; // let the component handle display
    }
  };

  return { login: loginUser };
}