import axios from 'axios';
import httpClient from '@/shared/api/httpClient';
import { sanctumClient } from '@/shared/api/httpClient';
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
  getCsrfCookie: async () => {
    await sanctumClient.get('/sanctum/csrf-cookie');
  },

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
    try {
      const { data } = await httpClient.get<{ user: User }>(endpoints.auth.user);
      return data.user;
    } catch (error: any) {
      if (axios.isAxiosError(error) && error.response?.status === 401) {
        return null;
      }
      throw error;
    }
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
};