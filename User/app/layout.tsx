import '@/styles/vendor/bootstrap.min.css';
import '@/styles/app.css';
import { AppProviders } from '@/providers/AppProviders';
import type { Metadata } from 'next';
import Script from 'next/script';

export const metadata: Metadata = {
  title: 'LaraBlog',
  description: 'A professional platform for sharing knowledge, ideas, and stories.',
};

export default function RootLayout({ children }: { children: React.ReactNode }) {
  return (
    <html lang="en" dir="ltr" data-theme="light" data-scroll-behavior="smooth" suppressHydrationWarning>
      <head>
        <link rel="stylesheet" href="/assets/FontAwesome/css/all.css" />
      </head>
      <body>
        <Script id="theme-script" strategy="beforeInteractive">
          {`
            try {
              var theme = localStorage.getItem('theme') || 'light';
              document.documentElement.setAttribute('data-theme', theme);
            } catch (e) {}
          `}
        </Script>
        <AppProviders>{children}</AppProviders>
      </body>
    </html>
  );
}