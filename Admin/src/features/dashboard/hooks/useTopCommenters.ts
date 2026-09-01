import { useQuery } from '@tanstack/react-query';
import { dashboardApi } from '../api/dashboardApi';
import { dashboardKeys } from './useDashboardStats';
import type { TopCommenter } from '../types/dashboard';

interface UseTopCommentersOptions {
  enabled?: boolean;
}

export function useTopCommenters({ enabled = true }: UseTopCommentersOptions = {}) {
  return useQuery<TopCommenter[]>({
    queryKey: dashboardKeys.commenters(),
    queryFn: dashboardApi.getTopCommenters,
    enabled,
  });
}