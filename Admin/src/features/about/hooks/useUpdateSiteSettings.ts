import { useMutation, useQueryClient } from '@tanstack/react-query';
import { aboutApi } from '../api/aboutApi';
import type { SiteSettings } from '../types/about';

export function useUpdateSiteSettings() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (data: Partial<SiteSettings>) => aboutApi.updateSettings(data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['about', 'settings'] });
    },
  });
}