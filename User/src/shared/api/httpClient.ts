import axios from 'axios';
import { env } from '@/shared/lib/env';

const httpClient = axios.create({
  baseURL: env.apiBaseUrl,
  withCredentials: true, // Sanctum cookie
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

// Optional: interceptor to handle 401 globally, refresh token logic if needed
httpClient.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      // Could emit an event to force logout
      window.dispatchEvent(new CustomEvent('auth:unauthorized'));
    }
    return Promise.reject(error);
  }
);

export default httpClient;