import { useMutation } from '@tanstack/react-query';
import { usersApi } from '../api/usersApi';
import type { UpdatePasswordPayload } from '../types/user';

export function useUpdateUserPassword() {
  return useMutation({
    mutationFn: ({ userId, payload }: { userId: string; payload: UpdatePasswordPayload }) =>
      usersApi.updatePassword(userId, payload),
  });
}