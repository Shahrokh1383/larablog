import { useEffect } from 'react';
import { useMutation, useQueryClient } from '@tanstack/react-query';
import { readerApi } from '../api/readerApi';
import { dashboardKeys } from '@/features/dashboard/hooks/useDashboardOverview';

export function useTrackPostRead(postId: string | undefined) {
  const queryClient = useQueryClient();

  const mutation = useMutation({
    mutationFn: (id: string) => readerApi.trackRead(id),
    onSuccess: () => {
      // Mark dashboard queries as stale. 
      // Next time the user visits the dashboard, it will instantly show cached data 
      // and seamlessly update with fresh data in the background (No spinners!).
      queryClient.invalidateQueries({ queryKey: dashboardKeys.overview() });
      queryClient.invalidateQueries({ queryKey: dashboardKeys.recentlyRead() });
    }
  });

  useEffect(() => {
    if (postId) {
      mutation.mutate(postId);
    }
  // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [postId]);
}