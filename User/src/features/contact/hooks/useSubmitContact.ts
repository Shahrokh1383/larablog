import { useState } from 'react';
import { useMutation } from '@tanstack/react-query';
import { useAuth } from '@/features/auth/context/AuthContext';
import { contactApi } from '../api/contactApi';
import type { ContactPayload, ContactFormData } from '../types/contact';

export function useSubmitContact() {
  const { isAuthenticated } = useAuth();
  const [formData, setFormData] = useState<ContactFormData>({
    name: '',
    email: '',
    subject: '',
    message: '',
  });

  const mutation = useMutation({
    mutationFn: (payload: ContactPayload) => contactApi.submit(payload),
    onSuccess: () => {
      setFormData({ name: '', email: '', subject: '', message: '' });
    },
  });

  const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) => {
    setFormData({ ...formData, [e.target.name]: e.target.value });
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    
    // Client-side validation mirroring backend rules
    if (!formData.subject || !formData.message) return;
    if (!isAuthenticated && (!formData.name || !formData.email)) return;

    // If authenticated, omit name/email from payload (backend pulls from Auth)
    const payload: ContactPayload = isAuthenticated
      ? { subject: formData.subject, message: formData.message }
      : formData;

    mutation.mutate(payload);
  };

  return {
    formData,
    onChange: handleChange,
    onSubmit: handleSubmit,
    isAuthenticated,
    isPending: mutation.isPending,
    isSuccess: mutation.isSuccess,
    isError: mutation.isError,
    errorMessage: (mutation.error as any)?.response?.data?.message || 'Failed to send message.',
    successMessage: mutation.data?.message,
  };
}