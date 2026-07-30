import { useAuth } from '../context/AuthContext';
import { useRouter } from 'next/navigation';

export function useRegister() {
  const { register } = useAuth();
  const router = useRouter();

  const registerUser = async (credentials: {
    name: string;
    email: string;
    password: string;
    password_confirmation: string;
  }) => {
    await register(credentials);
    router.push('/dashboard');
  };

  return { register: registerUser };
}