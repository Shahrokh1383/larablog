import { useQuery } from '@tanstack/react-query';
import { dashboardApi } from '../api/dashboardApi';
import type { DashboardStats } from '../types/dashboard';

interface UseDashboardStatsOptions {
  enabled?: boolean;
}

export function useDashboardStats({ enabled = true }: UseDashboardStatsOptions = {}) {
  return useQuery<DashboardStats>({
    queryKey: dashboardKeys.stats(),
    queryFn: dashboardApi.getStats,
    enabled,
  });
}

export const dashboardKeys = {
  all: ['dashboard'] as const,
  stats: () => [...dashboardKeys.all, 'stats'] as const,
  me: () => [...dashboardKeys.all, 'me'] as const,
  commenters: () => [...dashboardKeys.all, 'commenters'] as const,
};