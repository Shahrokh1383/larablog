import { useQuery } from '@tanstack/react-query';
import { aboutApi } from '../api/aboutApi';

export const aboutKeys = {
  all: ['about'] as const,
};

export function useAboutData() {
  return useQuery({
    queryKey: aboutKeys.all,
    queryFn: aboutApi.getAboutData,
  });
}