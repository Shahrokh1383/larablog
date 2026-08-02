import '@/styles/vendor/bootstrap.min.css'; // Moved to src/styles to guarantee load order
import '@/styles/app.css'; // Custom styles now safely override Bootstrap
import { AppProviders } from '@/providers/AppProviders';
import type { Metadata } from 'next';

export const metadata: Metadata = {
  title: 'LaraBlog',
  description: 'A professional platform for sharing knowledge, ideas, and stories.',
};

export default function RootLayout({ children }: { children: React.ReactNode }) {
  return (
    <html lang="en" dir="ltr" data-theme="light" suppressHydrationWarning>
      <head>
        {/* Inline script to prevent Dark Mode Flash (FOUC) */}
        <script
          dangerouslySetInnerHTML={{
            __html: `
              (function() {
                try {
                  var theme = localStorage.getItem('theme') || 'light';
                  document.documentElement.setAttribute('data-theme', theme);
                } catch (e) {}
              })();
            `,
          }}
        />
        {/* FontAwesome kept in public to avoid webfont path issues */}
        <link rel="stylesheet" href="/assets/FontAwesome/css/all.css" />
      </head>
      <body>
        <AppProviders>{children}</AppProviders>
      </body>
    </html>
  );
}