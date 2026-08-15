'use client';

import { createContext, useContext, useMemo } from 'react';
import { useQuery } from '@tanstack/react-query';
import { authApi, authKeys } from '../api/authApi';
import type { User } from '../types/auth';

interface AuthContextValue {
  user: User | null;
  isLoading: boolean;
  isAuthenticated: boolean;
}

const AuthContext = createContext<AuthContextValue | undefined>(undefined);

export function AuthProvider({ children }: { children: React.ReactNode }) {
  const isClient = typeof window !== 'undefined';

  const {
    data: user,
    isLoading: isQueryLoading,
    isError,
  } = useQuery({
    queryKey: authKeys.user(),
    queryFn: authApi.getUser,
    retry: false,
    staleTime: 5 * 60 * 1000,
    enabled: isClient,
  });

  // Force isLoading to true during SSR/initial render to prevent hydration mismatches
  const isLoading = !isClient || isQueryLoading;

  const value = useMemo(
    () => ({
      user: user ?? null,
      isLoading,
      isAuthenticated: !!user && !isError,
    }),
    [user, isLoading, isError]
  );

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}

export const useAuth = () => {
  const context = useContext(AuthContext);
  if (context === undefined) {
    throw new Error('useAuth must be used within an AuthProvider');
  }
  return context;
};