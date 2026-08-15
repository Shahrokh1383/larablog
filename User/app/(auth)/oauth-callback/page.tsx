'use client';

import { useOAuthCallback } from '@/features/auth/hooks/useOAuthCallback';

export default function OAuthCallbackPage() {
  const { isProcessing, error } = useOAuthCallback();

  if (isProcessing) {
    return (
      <div className="flex items-center justify-center min-h-screen">
        <div className="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500"></div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="flex flex-col items-center justify-center min-h-screen text-red-500 gap-4">
        <p>{error}</p>
        <a href="/login" className="text-blue-500 hover:underline">Back to Login</a>
      </div>
    );
  }

  return null;
}