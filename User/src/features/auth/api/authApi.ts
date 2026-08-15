import axios, { AxiosError } from 'axios';
import httpClient, { sanctumClient } from '@/shared/api/httpClient';
import { endpoints } from '@/shared/api/endpoints';
import type {
  LoginCredentials,
  RegisterCredentials,
  ForgotPasswordData,
  ResetPasswordData,
  AuthResponse,
  User,
  LaravelValidationError,
} from '../types/auth';

export const authKeys = {
  all: ['auth'] as const,
  user: () => [...authKeys.all, 'user'] as const,
};

export const authApi = {
  getCsrfCookie: async () => {
    await sanctumClient.get('/sanctum/csrf-cookie');
  },

  login: async (credentials: LoginCredentials) => {
    await authApi.getCsrfCookie();
    const { data } = await httpClient.post<AuthResponse>(endpoints.auth.login, credentials);
    return data;
  },

  register: async (credentials: RegisterCredentials) => {
    await authApi.getCsrfCookie();
    const { data } = await httpClient.post<AuthResponse>(endpoints.auth.register, credentials);
    return data;
  },

  logout: async () => {
    await authApi.getCsrfCookie();
    await httpClient.post(endpoints.auth.logout);
  },

  getUser: async (): Promise<User> => {
    const { data } = await httpClient.get<{ user: User }>(endpoints.auth.user);
    return data.user;
  },

  forgotPassword: async (payload: ForgotPasswordData) => {
    await authApi.getCsrfCookie();
    const { data } = await httpClient.post<{ message: string }>(
      endpoints.auth.forgotPassword,
      payload
    );
    return data;
  },

  resetPassword: async (payload: ResetPasswordData) => {
    await authApi.getCsrfCookie();
    const { data } = await httpClient.post<{ message: string }>(
      endpoints.auth.resetPassword,
      payload
    );
    return data;
  },

  verifyEmail: async (id: string, hash: string, params: Record<string, string | null>) => {
    const { data } = await httpClient.get<{ message: string; user?: User }>(
      endpoints.auth.emailVerify(id, hash),
      { params }
    );
    return data;
  },
};

export const isLaravelValidationError = (
  error: unknown
): error is AxiosError<LaravelValidationError> => {
  return axios.isAxiosError(error) && error.response?.data?.errors !== undefined;
};