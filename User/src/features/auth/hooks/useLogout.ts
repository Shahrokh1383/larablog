import { useAuth } from '../context/AuthContext';
import { useRouter } from 'next/navigation';

export function useLogout() {
  const { logout } = useAuth();
  const router = useRouter();

  const performLogout = async () => {
    await logout();
    router.push('/');
  };

  return { logout: performLogout };
}