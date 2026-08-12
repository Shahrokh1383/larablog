import type { Metadata } from 'next';
import { useAboutData } from '@/features/about/hooks/useAboutData';
import AboutHero from '@/features/about/components/AboutHero';
import AboutStory from '@/features/about/components/AboutStory';
import AboutValues from '@/features/about/components/AboutValues';
import AboutTeam from '@/features/about/components/AboutTeam';
import AboutCTA from '@/features/about/components/AboutCTA';
import '@/styles/about.css';

export const metadata: Metadata = {
  title: 'About Us — LaraBlog',
  description: 'About LaraBlog — our story, mission, and the team behind the platform.',
};

export default function AboutPage() {
  const { data, isLoading, isError } = useAboutData();

  if (isLoading) {
    return (
      <div className="d-flex justify-content-center align-items-center vh-100">
        <div className="spinner-border text-primary" role="status">
          <span className="visually-hidden">Loading...</span>
        </div>
      </div>
    );
  }

  if (isError || !data) {
    return (
      <div className="container text-center py-5">
        <h2 className="text-danger">Error loading page data</h2>
        <p>Please try again later.</p>
      </div>
    );
  }

  return (
    <main>
      <AboutHero />
      <AboutStory />
      <AboutValues />
      <AboutTeam members={data.team_members} />
      <AboutCTA />
    </main>
  );
}