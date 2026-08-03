import axios from 'axios';
import { env } from '@/shared/lib/env';

// Main client for all /api routes
const httpClient = axios.create({
  baseURL: env.apiBaseUrl,      // usually "/api"
  withCredentials: true,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

httpClient.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      window.dispatchEvent(new CustomEvent('auth:unauthorized'));
    }
    return Promise.reject(error);
  }
);

// Dedicated client for Sanctum CSRF cookie (NO /api prefix)
export const sanctumClient = axios.create({
  baseURL: '/',                // hits same origin, so /sanctum/csrf-cookie works
  withCredentials: true,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

export default httpClient;