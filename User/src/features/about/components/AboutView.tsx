'use client';

import { useAboutData } from '../hooks/useAboutData';
import AboutHero from './AboutHero';
import AboutStory from './AboutStory';
import AboutValues from './AboutValues';
import AboutTeam from './AboutTeam';
import AboutCTA from './AboutCTA';

export default function AboutView() {
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