import { useQuery } from '@tanstack/react-query';
import { profileApi } from '../api/profileApi';

export const profileKeys = {
  all: ['profile'] as const,
  detail: () => [...profileKeys.all, 'detail'] as const,
};

export function useProfile() {
  return useQuery({
    queryKey: profileKeys.detail(),
    queryFn: profileApi.get,
    staleTime: 1000 * 60 * 5, // 5 minutes
  });
}