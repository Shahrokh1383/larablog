import { useQuery } from '@tanstack/react-query';
import { aboutApi } from '../api/aboutApi';

export function useEligibleUsers() {
  return useQuery({
    queryKey: ['about', 'eligible-users'],
    queryFn: aboutApi.getEligibleUsers,
    staleTime: 30000,
  });
}