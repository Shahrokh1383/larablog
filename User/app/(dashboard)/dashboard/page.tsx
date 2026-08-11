'use client';

import { useState } from 'react';
import { useQueryClient } from '@tanstack/react-query';
import { useAuth } from '@/features/auth/context/AuthContext';
import { useDashboardOverview, dashboardKeys } from '@/features/dashboard/hooks/useDashboardOverview';
import { dashboardApi } from '@/features/dashboard/api/dashboardApi';
import { useRecentlyRead } from '@/features/dashboard/hooks/useRecentlyRead';
import { useUserComments } from '@/features/dashboard/hooks/useUserComments';
import { useSavedPosts, readerKeys } from '@/features/reader/hooks/useSavedPosts';
import { readerApi } from '@/features/reader/api/readerApi';
import { useUnsavePost } from '@/features/reader/hooks/useUnsavePost';
import DashboardHeader from '@/features/dashboard/components/DashboardHeader';
import OverviewTab from '@/features/dashboard/components/OverviewTab';
import CommentsTab from '@/features/dashboard/components/CommentsTab';
import BookmarksTab from '@/features/dashboard/components/BookmarksTab';
import SettingsTab from '@/features/dashboard/components/SettingsTab';
import '@/styles/dashboard.css';

type TabId = 'overview' | 'comments' | 'bookmarks' | 'settings';

export default function DashboardPage() {
  const [activeTab, setActiveTab] = useState<TabId>('overview');
  const [settingsVisited, setSettingsVisited] = useState(false);
  
  const { user } = useAuth();
  const queryClient = useQueryClient();
  
  const [recentlyReadPage, setRecentlyReadPage] = useState(1);
  const [commentsPage, setCommentsPage] = useState(1);
  const [bookmarksPage, setBookmarksPage] = useState(1);

  // 1. PRIMARY QUERY: Fires immediately on mount
  const overviewQuery = useDashboardOverview();

  // 2. SEQUENTIAL QUERIES: Disabled until Overview succeeds.
  // This prevents the `php artisan serve` single-thread deadlock.
  const recentlyReadQuery = useRecentlyRead(recentlyReadPage, { 
    enabled: activeTab === 'overview' && overviewQuery.isSuccess 
  });
  
  const commentsQuery = useUserComments(commentsPage, { 
    enabled: activeTab === 'comments' && !!user 
  });
  
  const savedPostsQuery = useSavedPosts(bookmarksPage, { 
    enabled: activeTab === 'bookmarks' && !!user 
  });
  
  const unsaveMutation = useUnsavePost();
  const handleUnsave = (postId: string) => unsaveMutation.mutate(postId);

  // Prefetch data on hover for instant perceived load times
  const handleTabHover = (tab: TabId) => {
    if (tab === 'comments' && !commentsQuery.data) {
      queryClient.prefetchQuery({
        queryKey: [...dashboardKeys.comments(), 1],
        queryFn: () => dashboardApi.getUserComments(1)
      });
    }
    if (tab === 'bookmarks' && !savedPostsQuery.data) {
      // FIX: Use readerApi and readerKeys (Saved Posts belong to ReaderExperience module)
      queryClient.prefetchQuery({
        queryKey: [...readerKeys.savedPosts(), 1],
        queryFn: () => readerApi.getSavedPosts(1)
      });
    }
  };

  const handleTabClick = (tab: TabId) => {
    setActiveTab(tab);
    if (tab === 'settings') setSettingsVisited(true);
    if (tab === 'comments') setCommentsPage(1);
    if (tab === 'bookmarks') setBookmarksPage(1);
    if (tab === 'overview') setRecentlyReadPage(1);
  };

  if (!user) {
    return (
      <div className="d-flex justify-content-center align-items-center" style={{ minHeight: '60vh' }}>
        <div className="spinner-border text-primary" role="status">
          <span className="visually-hidden">Loading...</span>
        </div>
      </div>
    );
  }

  return (
    <main className="dashboard-page">
      <div className="container dashboard-container">
        <DashboardHeader user={user} overview={overviewQuery.data} />

        <div className="dashboard-tabs">
          <button 
            className={`tab-btn ${activeTab === 'overview' ? 'active' : ''}`} 
            onClick={() => handleTabClick('overview')}
          >
            <i className="fa-sharp fa-solid fa-grid-2"></i> Overview
          </button>
          <button 
            className={`tab-btn ${activeTab === 'comments' ? 'active' : ''}`} 
            onClick={() => handleTabClick('comments')}
            onMouseEnter={() => handleTabHover('comments')}
          >
            <i className="fa-sharp fa-solid fa-comments"></i> My Comments
          </button>
          <button 
            className={`tab-btn ${activeTab === 'bookmarks' ? 'active' : ''}`} 
            onClick={() => handleTabClick('bookmarks')}
            onMouseEnter={() => handleTabHover('bookmarks')}
          >
            <i className="fa-sharp fa-solid fa-bookmark"></i> Saved Posts
          </button>
          <button 
            className={`tab-btn ${activeTab === 'settings' ? 'active' : ''}`} 
            onClick={() => handleTabClick('settings')}
          >
            <i className="fa-sharp fa-solid fa-gear"></i> Settings
          </button>
        </div>

        <div className="tab-content-wrapper">
          {activeTab === 'overview' && (
            <OverviewTab 
              overview={overviewQuery.data} 
              recentlyRead={recentlyReadQuery.data}
              isLoading={overviewQuery.isPending || recentlyReadQuery.isPending}
              onPageChange={setRecentlyReadPage}
            />
          )}
          {activeTab === 'comments' && (
            <CommentsTab 
              user={user}
              comments={commentsQuery.data}
              isLoading={commentsQuery.isPending}
              onPageChange={setCommentsPage}
            />
          )}
          {activeTab === 'bookmarks' && (
            <BookmarksTab 
              savedPosts={savedPostsQuery.data}
              isLoading={savedPostsQuery.isPending}
              onPageChange={setBookmarksPage}
              onUnsave={handleUnsave}
            />
          )}
          {activeTab === 'settings' && (
            <SettingsTab hasBeenActive={settingsVisited} />
          )}
        </div>
      </div>
    </main>
  );
}