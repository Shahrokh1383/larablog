import { useMutation, useQueryClient } from '@tanstack/react-query';
import { usersApi } from '../api/usersApi';
import type { UpdateRolePayload } from '../types/user';
import { userKeys } from './useUsers';
import { aboutKeys } from '@/features/about/hooks/useEligibleUsers';

export function useUpdateUserRole() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: ({ userId, payload }: { userId: string; payload: UpdateRolePayload }) =>
      usersApi.updateRole(userId, payload),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: userKeys.lists() });
      queryClient.invalidateQueries({ queryKey: aboutKeys.eligibleUsers });
    },
  });
}