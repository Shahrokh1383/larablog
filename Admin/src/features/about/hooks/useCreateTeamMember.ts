import { useMutation, useQueryClient } from '@tanstack/react-query';
import { aboutApi } from '../api/aboutApi';

type CreatePayload = { user_id: string; sort_order: number; is_active: boolean };

export function useCreateTeamMember() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (payload: CreatePayload) => aboutApi.createTeamMember(payload),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['about', 'team-members'] });
    },
  });
}