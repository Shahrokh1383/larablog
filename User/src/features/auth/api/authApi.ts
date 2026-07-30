import httpClient from '@/shared/api/httpClient';
import { endpoints } from '@/shared/api/endpoints';
import type {
  LoginCredentials,
  RegisterCredentials,
  ForgotPasswordData,
  ResetPasswordData,
  AuthResponse,
  User,
} from '../types/auth';

export const authApi = {
  login: async (credentials: LoginCredentials) => {
    const { data } = await httpClient.post<AuthResponse>(endpoints.auth.login, credentials);
    return data;
  },
  register: async (credentials: RegisterCredentials) => {
    const { data } = await httpClient.post<AuthResponse>(endpoints.auth.register, credentials);
    return data;
  },
  logout: async () => {
    await httpClient.post(endpoints.auth.logout);
  },
  getUser: async () => {
    // This endpoint doesn't exist yet; you need to add a /api/user route.
    // Until then, we fetch from a dummy or rely on the user stored in cache.
    const { data } = await httpClient.get<User>(endpoints.auth.user);
    return data;
  },
  getUserWithToken: async (token: string) => {
    const { data } = await httpClient.get<User>('/user', {
      headers: {
        Authorization: `Bearer ${token}`,
      },
    });
    return data;
  },
  forgotPassword: async (payload: ForgotPasswordData) => {
    const { data } = await httpClient.post<{ message: string }>(
      endpoints.auth.forgotPassword,
      payload
    );
    return data;
  },
  resetPassword: async (payload: ResetPasswordData) => {
    const { data } = await httpClient.post<{ message: string }>(
      endpoints.auth.resetPassword,
      payload
    );
    return data;
  },

  verifyEmail: async (id: string, hash: string, params: Record<string, string | null>) => {
    const { data } = await httpClient.get<{ message: string }>(
      endpoints.auth.emailVerify(id, hash),
      { params }
    );
    return data;
  },
  // OAuth redirect is a simple window.location redirect; no fetch needed.
};