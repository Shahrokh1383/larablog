'use client';

import { useEffect } from 'react';
import { useRouter } from 'next/navigation';
import { useAuth } from '@/features/auth/context/AuthContext';
import Header from '@/shared/components/layout/Header';
import Footer from '@/shared/components/layout/Footer';

export default function DashboardLayout({ children }: { children: React.ReactNode }) {
  const { isAuthenticated, isLoading } = useAuth();
  const router = useRouter();

  useEffect(() => {
    // If auth state is loaded and user is NOT authenticated, kick them out
    if (!isLoading && !isAuthenticated) {
      router.replace('/login');
    }
  }, [isLoading, isAuthenticated, router]);

  // Show a loading spinner while checking auth state or redirecting
  if (isLoading || !isAuthenticated) {
    return (
      <div className="d-flex justify-content-center align-items-center" style={{ minHeight: '100vh' }}>
        <div className="spinner-border text-primary" role="status">
          <span className="visually-hidden">Loading...</span>
        </div>
      </div>
    );
  }

  // Render the dashboard with the site header and footer
  return (
    <>
      <Header />
      <main className="dashboard-page">
        {children}
      </main>
      <Footer />
    </>
  );
}