'use client';

import type { DashboardOverview, RecentlyReadItem } from '../api/dashboardApi';
import type { PaginatedResponse } from '@/shared/types/api';
import Pagination from './Pagination';

interface OverviewTabProps {
  overview?: DashboardOverview;
  recentlyRead?: PaginatedResponse<RecentlyReadItem>;
  isLoading: boolean;
  onPageChange: (page: number) => void;
}

export default function OverviewTab({ overview, recentlyRead, isLoading, onPageChange }: OverviewTabProps) {
  if (isLoading || !overview) {
    return <div className="text-center py-5"><div className="spinner-border text-primary"></div></div>;
  }

  const formatTime = (mins: number) => {
    const h = Math.floor(mins / 60);
    const m = mins % 60;
    return `${h > 0 ? `${h}h ` : ''}${m}m`;
  };

  return (
    <div className="tab-content active" id="overviewContent">
      <div className="row g-4">
        <div className="col-lg-8">
          <div className="dashboard-card welcome-card">
            <div className="card-body">
              <h3 className="card-title">Welcome back! 👋</h3>
              <p className="card-text">
                Here's a quick snapshot of your recent reading activity. You’ve read <strong>{overview.posts_read_count} articles</strong> this week and left <strong>{overview.comments_count} new comments</strong>.
              </p>
              <div className="quick-stats">
                <div className="quick-stat">
                  <i className="fa-sharp fa-solid fa-book-open-reader"></i>
                  <div>
                    <strong>{overview.posts_read_count}</strong>
                    <span>Articles read this week</span>
                  </div>
                </div>
                <div className="quick-stat">
                  <i className="fa-sharp fa-solid fa-clock"></i>
                  <div>
                    <strong>{formatTime(overview.total_reading_time)}</strong>
                    <span>Total reading time</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        {overview.is_top_commenter && (
          <div className="col-lg-4">
            <div className="dashboard-card achievement-card">
              <div className="card-body text-center">
                <div className="achievement-icon">
                  <i className="fa-sharp fa-solid fa-medal"></i>
                </div>
                <h4 className="achievement-title">Top Commenter</h4>
                <p className="achievement-desc">You’re among the most active commenters this month!</p>
              </div>
            </div>
          </div>
        )}
      </div>

      <div className="section-header mt-5">
        <h3>Recently Read</h3>
      </div>

      {recentlyRead?.data && recentlyRead.data.length > 0 ? (
        <>
          <div className="row g-4">
            {recentlyRead.data.map((item) => (
              <div className="col-lg-4 col-md-6" key={item.id}>
                <article className="post-card post-card-standard">
                  <div className="post-card-image">
                    <img src={item.post.featured_image || `https://picsum.photos/seed/${item.post.id}/600/350`} alt={item.post.title} loading="lazy" />
                  </div>
                  <div className="post-card-body">
                    <div className="post-card-meta">
                      <span className="post-card-read-time">{item.post.reading_time} min read</span>
                    </div>
                    <h3 className="post-card-title">
                      <a href={`/post/${item.post.slug}`}>{item.post.title}</a>
                    </h3>
                  </div>
                </article>
              </div>
            ))}
          </div>
          <Pagination 
            currentPage={recentlyRead.meta.current_page} 
            lastPage={recentlyRead.meta.last_page} 
            onPageChange={onPageChange} 
          />
        </>
      ) : (
        <p className="text-muted text-center mt-4">No recently read articles.</p>
      )}
    </div>
  );
}