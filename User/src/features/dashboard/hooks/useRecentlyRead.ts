import { useQuery } from '@tanstack/react-query';
import { dashboardApi } from '../api/dashboardApi';
import { dashboardKeys } from './useDashboardOverview';

export function useRecentlyRead(page = 1) {
  return useQuery({
    queryKey: [...dashboardKeys.recentlyRead(), page],
    queryFn: () => dashboardApi.getRecentlyRead(page),
    staleTime: 1000 * 60 * 5, // Data stays fresh for 5 mins
    gcTime: 1000 * 60 * 30,   // Keep in memory for 30 mins
  });
}