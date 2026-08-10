import { useQuery, keepPreviousData } from '@tanstack/react-query';
import { dashboardApi } from '../api/dashboardApi';
import { dashboardKeys } from './useDashboardOverview';

interface UseUserCommentsOptions {
  enabled?: boolean;
}

export function useUserComments(page: number = 1, options?: UseUserCommentsOptions) {
  return useQuery({
    queryKey: [...dashboardKeys.comments(), page],
    queryFn: () => dashboardApi.getUserComments(page),
    staleTime: 1000 * 60 * 5,
    gcTime: 1000 * 60 * 30,
    enabled: options?.enabled ?? true,
    placeholderData: keepPreviousData, // Keeps old data visible while fetching new page
  });
}