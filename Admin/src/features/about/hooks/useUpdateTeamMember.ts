import { useMutation, useQueryClient } from '@tanstack/react-query';
import { aboutApi } from '../api/aboutApi';

export function useUpdateTeamMember() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: ({ id, formData }: { id: string; formData: FormData }) =>
      aboutApi.updateTeamMember(id, formData),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['about', 'team-members'] });
    },
  });
}