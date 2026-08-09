import { useQuery } from '@tanstack/react-query';
import { dashboardApi } from '../api/dashboardApi';
import { dashboardKeys } from './useDashboardOverview';

export function useUserComments(page = 1) {
  return useQuery({
    queryKey: [...dashboardKeys.comments(), page],
    queryFn: () => dashboardApi.getUserComments(page),
  });
}