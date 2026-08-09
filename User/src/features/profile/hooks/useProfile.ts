import { useQuery } from '@tanstack/react-query';
import { profileApi } from '../api/profileApi';

export const profileKeys = {
  all: ['profile'] as const,
  detail: () => [...profileKeys.all, 'detail'] as const,
};

interface UseProfileOptions {
  enabled?: boolean;
}

export function useProfile(options?: UseProfileOptions) {
  return useQuery({
    queryKey: profileKeys.detail(),
    queryFn: profileApi.getProfile,
    staleTime: 1000 * 60 * 5,
    gcTime: 1000 * 60 * 30,
    enabled: options?.enabled ?? true,
  });
}