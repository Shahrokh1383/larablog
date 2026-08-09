import { useQuery } from '@tanstack/react-query';
import { dashboardApi } from '../api/dashboardApi';
import { dashboardKeys } from './useDashboardOverview';

interface UseRecentlyReadOptions {
  enabled?: boolean;
}

export function useRecentlyRead(page: number = 1, options?: UseRecentlyReadOptions) {
  return useQuery({
    queryKey: [...dashboardKeys.recentlyRead(), page],
    queryFn: () => dashboardApi.getRecentlyRead(page),
    staleTime: 1000 * 60 * 5, // 5 minutes (Prevents aggressive refetching)
    gcTime: 1000 * 60 * 30,   // 30 minutes (Persistent memory caching)
    enabled: options?.enabled ?? true, // Defer fetching until tab is visited
  });
}