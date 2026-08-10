import { useQuery, keepPreviousData } from '@tanstack/react-query';
import { dashboardApi } from '../api/dashboardApi';
import { dashboardKeys } from './useDashboardOverview';

interface UseRecentlyReadOptions {
  enabled?: boolean;
}

export function useRecentlyRead(page: number = 1, options?: UseRecentlyReadOptions) {
  return useQuery({
    queryKey: [...dashboardKeys.recentlyRead(), page],
    queryFn: () => dashboardApi.getRecentlyRead(page),
    staleTime: 1000 * 60 * 5, 
    gcTime: 1000 * 60 * 30,  
    enabled: options?.enabled ?? true, 
    placeholderData: keepPreviousData, // Keeps old data visible
  });
}