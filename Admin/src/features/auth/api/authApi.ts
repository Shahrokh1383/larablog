import httpClient from '@/shared/api/httpClient';
import type { LoginCredentials, LoginResponse, AuthUser } from '../types/auth';

export const authApi = {
  login: async (credentials: LoginCredentials): Promise<LoginResponse> => {
    const response = await httpClient.post('/login', credentials);
    return response.data; // { user, token }
  },

  logout: async (): Promise<void> => {
    await httpClient.post('/logout');
  },

  getUser: async (): Promise<AuthUser> => {
    const response = await httpClient.get<{ user: AuthUser }>('/user');
    return response.data.user; // the API returns { user: UserResource }
  },
};