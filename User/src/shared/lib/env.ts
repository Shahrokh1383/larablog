export const env = {
  apiBaseUrl: process.env.NEXT_PUBLIC_API_BASE_URL || 'http://localhost:8000/api',
  appUrl: process.env.NEXT_PUBLIC_APP_URL || 'http://localhost:3000',
  sanctumCookie: process.env.NEXT_PUBLIC_SANCTUM_COOKIE || 'laravel_session',
};