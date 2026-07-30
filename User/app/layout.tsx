import '@/styles/app.css';
import { AppProviders } from '@/providers/AppProviders';
import type { Metadata } from 'next';

export const metadata: Metadata = {
  title: 'LaraBlog',
  description: '...',
};

export default function RootLayout({ children }: { children: React.ReactNode }) {
  return (
    <html lang="en" dir="ltr" data-theme="light">
      <head>
        {/* Local Bootstrap & FontAwesome */}
        <link rel="stylesheet" href="/assets/bootstrap-pack/css/bootstrap.min.css" />
        <link rel="stylesheet" href="/assets/FontAwesome/css/all.css" />
      </head>
      <body>
        <AppProviders>{children}</AppProviders>
      </body>
    </html>
  );
}