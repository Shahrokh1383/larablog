'use client';

import { useAboutData } from '../hooks/useAboutData';
import AboutHero from './AboutHero';
import AboutStory from './AboutStory';
import AboutValues from './AboutValues';
import AboutTeam from './AboutTeam';
import AboutCTA from './AboutCTA';
import Pagination from '@/shared/components/Pagination';

export default function AboutView() {
  const { data, isLoading, isError, teamPage, setTeamPage } = useAboutData();

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
      <AboutStory storyImage={data.settings.story_image} />
      <AboutValues />
      <AboutTeam members={data.team_members} />
      {data.team_members_pagination && data.team_members_pagination.last_page > 1 && (
        <div className="container pb-5">
          <Pagination
            currentPage={data.team_members_pagination.current_page}
            lastPage={data.team_members_pagination.last_page}
            onPageChange={(page) => setTeamPage(page)}
          />
        </div>
      )}
      <AboutCTA />
    </main>
  );
}