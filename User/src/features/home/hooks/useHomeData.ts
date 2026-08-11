import { useQuery } from '@tanstack/react-query';
import { homeApi } from '../api/homeApi';

export const homeKeys = {
  all: ['home'] as const,
};

export function useHomeData() {
  return useQuery({
    queryKey: homeKeys.all,
    queryFn: homeApi.getHomeData,
  });
}