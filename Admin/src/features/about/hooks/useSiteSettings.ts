import { useQuery } from '@tanstack/react-query';
import { aboutApi } from '../api/aboutApi';

export function useSiteSettings() {
  return useQuery({
    queryKey: ['about', 'settings'],
    queryFn: aboutApi.getSettings,
  });
}