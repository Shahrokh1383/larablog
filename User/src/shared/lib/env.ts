export const env = {
  apiBaseUrl: process.env.NEXT_PUBLIC_API_BASE_URL || '/api',
  appUrl: process.env.NEXT_PUBLIC_APP_URL || 'http://localhost:3000',
  sanctumCookie: process.env.NEXT_PUBLIC_SANCTUM_COOKIE || 'laravel_session',
  reverbAppKey: process.env.NEXT_PUBLIC_REVERB_APP_KEY || '',
  reverbHost: process.env.NEXT_PUBLIC_REVERB_HOST || 'localhost',
  reverbPort: process.env.NEXT_PUBLIC_REVERB_PORT || '8080',
};