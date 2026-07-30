'use client';

import { QueryProvider } from './QueryProvider';
import { AuthProvider } from '@/features/auth/context/AuthContext';

export function AppProviders({ children }: { children: React.ReactNode }) {
  return (
    <QueryProvider>
      <AuthProvider>{children}</AuthProvider>
    </QueryProvider>
  );
}