import { useQuery } from '@tanstack/react-query';
import { dashboardApi } from '../api/dashboardApi';
import { dashboardKeys } from './useDashboardStats';
import type { AuthorStats } from '../types/dashboard';

interface UseAuthorStatsOptions {
  enabled?: boolean;
}

export function useAuthorStats({ enabled = true }: UseAuthorStatsOptions = {}) {
  return useQuery<AuthorStats>({
    queryKey: dashboardKeys.me(),
    queryFn: dashboardApi.getMyStats,
    enabled,
  });
}