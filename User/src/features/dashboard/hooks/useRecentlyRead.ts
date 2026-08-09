import { useQuery } from '@tanstack/react-query';
import { dashboardApi } from '../api/dashboardApi';
import { dashboardKeys } from './useDashboardOverview';

export function useRecentlyRead(page = 1) {
  return useQuery({
    queryKey: [...dashboardKeys.recentlyRead(), page],
    queryFn: () => dashboardApi.getRecentlyRead(page),
  });
}