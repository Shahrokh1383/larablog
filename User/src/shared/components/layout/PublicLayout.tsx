'use client';

import Header from './Header';
import Footer from './Footer';
import { useAboutData } from '@/features/about';

export default function PublicLayout({ children }: { children: React.ReactNode }) {
  const { data } = useAboutData();

  return (
    <>
      <Header />
      <main>{children}</main>
      <Footer settings={data?.settings} />
    </>
  );
}