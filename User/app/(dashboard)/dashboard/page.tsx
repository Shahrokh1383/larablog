'use client';

import { useState } from 'react';
import { useQueryClient } from '@tanstack/react-query';
import { useAuth } from '@/features/auth/context/AuthContext';
import { useDashboardOverview, dashboardKeys } from '@/features/dashboard/hooks/useDashboardOverview';
import { dashboardApi } from '@/features/dashboard/api/dashboardApi';
import { useRecentlyRead } from '@/features/dashboard/hooks/useRecentlyRead';
import { useUserComments } from '@/features/dashboard/hooks/useUserComments';
import { useSavedPosts } from '@/features/reader/hooks/useSavedPosts';
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
  const [settingsVisited, setSettingsVisited] = useState(false); // Track if settings tab has been activated
  
  const { user } = useAuth();
  const queryClient = useQueryClient();
  
  const [recentlyReadPage, setRecentlyReadPage] = useState(1);
  const [commentsPage, setCommentsPage] = useState(1);
  const [bookmarksPage, setBookmarksPage] = useState(1);

  // Fetch data strictly for the active tab to save bandwidth and prevent network waterfalls
  const overviewQuery = useDashboardOverview();
  const recentlyReadQuery = useRecentlyRead(recentlyReadPage, { enabled: activeTab === 'overview' });
  const commentsQuery = useUserComments(commentsPage, { enabled: activeTab === 'comments' });
  const savedPostsQuery = useSavedPosts(bookmarksPage, { enabled: activeTab === 'bookmarks' });
  
  const unsaveMutation = useUnsavePost();
  const handleUnsave = (postId: string) => unsaveMutation.mutate(postId);

  // Prefetch data on hover for instant 2-3ms perceived load times
  const handleTabHover = (tab: TabId) => {
    if (tab === 'comments') {
      queryClient.prefetchQuery({
        queryKey: [...dashboardKeys.comments(), 1],
        queryFn: () => dashboardApi.getUserComments(1)
      });
    }
    // Add similar prefetch logic for other tabs if desired
  };

  const handleTabClick = (tab: TabId) => {
    setActiveTab(tab);
    
    // Latch the settingsVisited flag to true once visited
    if (tab === 'settings') {
      setSettingsVisited(true);
    }
    
    // Reset to page 1 when switching tabs to ensure fresh pagination state
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
          <div style={{ display: activeTab === 'overview' ? 'block' : 'none' }}>
            <OverviewTab 
              overview={overviewQuery.data} 
              recentlyRead={recentlyReadQuery.data}
              isLoading={overviewQuery.isPending || recentlyReadQuery.isPending}
              onPageChange={setRecentlyReadPage}
            />
          </div>
          <div style={{ display: activeTab === 'comments' ? 'block' : 'none' }}>
            <CommentsTab 
              user={user}
              comments={commentsQuery.data}
              isLoading={commentsQuery.isPending}
              onPageChange={setCommentsPage}
            />
          </div>
          <div style={{ display: activeTab === 'bookmarks' ? 'block' : 'none' }}>
            <BookmarksTab 
              savedPosts={savedPostsQuery.data}
              isLoading={savedPostsQuery.isPending}
              onPageChange={setBookmarksPage}
              onUnsave={handleUnsave}
            />
          </div>
          <div style={{ display: activeTab === 'settings' ? 'block' : 'none' }}>
            <SettingsTab hasBeenActive={settingsVisited} />
          </div>
        </div>
      </div>
    </main>
  );
}