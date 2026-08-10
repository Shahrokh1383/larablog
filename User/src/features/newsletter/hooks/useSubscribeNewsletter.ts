import { useState } from 'react';
import { useMutation } from '@tanstack/react-query';
import { newsletterApi } from '../api/newsletterApi';

export function useSubscribeNewsletter() {
  const [email, setEmail] = useState('');

  const mutation = useMutation({
    mutationFn: (emailToSubmit: string) => newsletterApi.subscribe({ email: emailToSubmit }),
    onSuccess: () => {
      setEmail(''); // Clear input on success
    },
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (email.trim()) {
      mutation.mutate(email);
    }
  };

  return {
    email,
    onEmailChange: setEmail,
    onSubmit: handleSubmit,
    isPending: mutation.isPending,
    isSuccess: mutation.isSuccess,
    isError: mutation.isError,
    message: mutation.isSuccess 
      ? mutation.data?.message 
      : mutation.isError 
        ? (mutation.error as any)?.response?.data?.message || 'Failed to subscribe.' 
        : undefined,
  };
}