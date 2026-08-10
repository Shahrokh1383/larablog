import { useMutation } from '@tanstack/react-query';
import { contactApi, ContactPayload } from '../api/contactApi';

export function useSubmitContact() {
  return useMutation({
    mutationFn: (payload: ContactPayload) => contactApi.submit(payload),
  });
}