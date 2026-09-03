import { useMutation, useQueryClient } from '@tanstack/react-query';
import { aboutApi } from '../api/aboutApi';
import { aboutKeys } from './useEligibleUsers';

export function useDeleteTeamMember() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (id: string) => aboutApi.deleteTeamMember(id),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['about', 'team-members'] });
      queryClient.invalidateQueries({ queryKey: aboutKeys.eligibleUsers });
    },
  });
}