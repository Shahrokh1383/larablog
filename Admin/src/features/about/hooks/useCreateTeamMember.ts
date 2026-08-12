import { useMutation, useQueryClient } from '@tanstack/react-query';
import { aboutApi } from '../api/aboutApi';

export function useCreateTeamMember() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (formData: FormData) => aboutApi.createTeamMember(formData),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['about', 'team-members'] });
    },
  });
}