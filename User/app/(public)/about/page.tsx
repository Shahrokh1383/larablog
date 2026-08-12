import type { Metadata } from 'next';
import AboutView from '@/features/about/components/AboutView';
import '@/styles/about.css';

export const metadata: Metadata = {
  title: 'About Us — LaraBlog',
  description: 'About LaraBlog — our story, mission, and the team behind the platform.',
};

export default function AboutPage() {
  return <AboutView />;
}