import { useQuery } from '@tanstack/react-query';
import { dashboardApi } from '../api/dashboardApi';

export function useRecentlyRead(page = 1) {
  return useQuery({
    queryKey: [...dashboardKeys.recentlyRead(), page],
    queryFn: () => dashboardApi.getRecentlyRead(page),
  });
}