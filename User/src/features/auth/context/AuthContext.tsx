'use client';

import { createContext, useContext, useCallback, useMemo } from 'react';
import { useQueryClient } from '@tanstack/react-query';
import { useQuery, useMutation } from '@tanstack/react-query';
import { authApi } from '../api/authApi';
import type { User, LoginCredentials, RegisterCredentials } from '../types/auth';

interface AuthContextValue {
  user: User | null;
  isLoading: boolean;
  isAuthenticated: boolean;
  login: (credentials: LoginCredentials) => Promise<User>;
  register: (credentials: RegisterCredentials) => Promise<User>;
  logout: () => Promise<void>;
  setUser: (user: User | null) => void;
}

const AuthContext = createContext<AuthContextValue | undefined>(undefined);

export function AuthProvider({ children }: { children: React.ReactNode }) {
  const queryClient = useQueryClient();

  // Fetch current user if we have a valid session (requires backend /api/user endpoint)
  const {
    data: user,
    isLoading,
    isError,
  } = useQuery({
    queryKey: ['auth', 'user'],
    queryFn: authApi.getUser,
    retry: false,
    staleTime: 5 * 60 * 1000,
    enabled: true, // Always attempt on mount; if unauthenticated, backend returns 401
  });

  const loginMutation = useMutation({
    mutationFn: authApi.login,
    onSuccess: (data) => {
      queryClient.setQueryData(['auth', 'user'], data.user);
      // token is stored in Sanctum cookie automatically
    },
  });

  const registerMutation = useMutation({
    mutationFn: authApi.register,
    onSuccess: (data) => {
      queryClient.setQueryData(['auth', 'user'], data.user);
    },
  });

  const logoutMutation = useMutation({
    mutationFn: authApi.logout,
    onSuccess: () => {
      queryClient.setQueryData(['auth', 'user'], null);
      queryClient.invalidateQueries({ queryKey: ['auth'] });
    },
  });

  const login = useCallback(
    async (credentials: LoginCredentials) => {
      const { user } = await loginMutation.mutateAsync(credentials);
      return user;
    },
    [loginMutation]
  );

  const register = useCallback(
    async (credentials: RegisterCredentials) => {
      const { user } = await registerMutation.mutateAsync(credentials);
      return user;
    },
    [registerMutation]
  );

  const logout = useCallback(async () => {
    await logoutMutation.mutateAsync();
  }, [logoutMutation]);

  const setUser = useCallback(
    (newUser: User | null) => {
      queryClient.setQueryData(['auth', 'user'], newUser);
    },
    [queryClient]
  );

  const value = useMemo(
    () => ({
      user: user ?? null,
      isLoading,
      isAuthenticated: !!user && !isError,
      login,
      register,
      logout,
      setUser,
    }),
    [user, isLoading, isError, login, register, logout, setUser]
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