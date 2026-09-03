import { useMutation, useQueryClient } from '@tanstack/react-query';
import { aboutApi } from '../api/aboutApi';
import { aboutKeys } from './useEligibleUsers';

type UpdatePayload = {
  id: string;
  user_id?: string;
  sort_order?: number;
  is_active?: boolean;
};

export function useUpdateTeamMember() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: ({ id, ...rest }: UpdatePayload) => aboutApi.updateTeamMember(id, rest),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['about', 'team-members'] });
      queryClient.invalidateQueries({ queryKey: aboutKeys.eligibleUsers });
    },
  });
}