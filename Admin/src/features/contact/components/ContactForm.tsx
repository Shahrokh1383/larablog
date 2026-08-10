'use client';
import { useState } from 'react';
import { useSubmitContact } from '../hooks/useContact';
import { useAdminAuth } from '@/features/auth/hooks/useAdminAuth';

export default function ContactForm() {
  const { user } = useAdminAuth();
  const isAuthenticated = !!user;

  const [formData, setFormData] = useState({
    name: user?.name || '',
    email: user?.email || '',
    subject: '',
    message: '',
  });

  const mutation = useSubmitContact();

  const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) => {
    setFormData({ ...formData, [e.target.name]: e.target.value });
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    const payload = isAuthenticated 
      ? { subject: formData.subject, message: formData.message }
      : formData;

    mutation.mutate(payload, {
      onSuccess: () => setFormData({ name: '', email: '', subject: '', message: '' })
    });
  };

  return (
    <form onSubmit={handleSubmit} className="space-y-4 max-w-2xl mx-auto">
      {!isAuthenticated && (
        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">Your Name</label>
            <input type="text" name="name" required value={formData.name} onChange={handleChange} className="w-full px-4 py-2 border border-gray-300 rounded-md" />
          </div>
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
            <input type="email" name="email" required value={formData.email} onChange={handleChange} className="w-full px-4 py-2 border border-gray-300 rounded-md" />
          </div>
        </div>
      )}

      <div>
        <label className="block text-sm font-medium text-gray-700 mb-1">Subject</label>
        <input type="text" name="subject" required value={formData.subject} onChange={handleChange} className="w-full px-4 py-2 border border-gray-300 rounded-md" />
      </div>

      <div>
        <label className="block text-sm font-medium text-gray-700 mb-1">Message</label>
        <textarea name="message" required rows={6} value={formData.message} onChange={handleChange} className="w-full px-4 py-2 border border-gray-300 rounded-md"></textarea>
      </div>

      <button type="submit" disabled={mutation.isPending} className="w-full py-3 bg-blue-600 text-white font-semibold rounded-md hover:bg-blue-700 disabled:opacity-50">
        {mutation.isPending ? 'Sending...' : 'Send Message'}
      </button>

      {mutation.isSuccess && <p className="text-green-600 text-center">{mutation.data.message}</p>}
      {mutation.isError && <p className="text-red-600 text-center">Failed to send message. Please try again.</p>}
    </form>
  );
}