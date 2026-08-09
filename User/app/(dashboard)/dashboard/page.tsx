'use client';

import { useState } from 'react';
import { useAuth } from '@/features/auth/context/AuthContext';
import { useDashboardOverview } from '@/features/dashboard/hooks/useDashboardOverview';
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
  const { user } = useAuth();
  
  const [recentlyReadPage, setRecentlyReadPage] = useState(1);
  const [commentsPage, setCommentsPage] = useState(1);
  const [bookmarksPage, setBookmarksPage] = useState(1);

  const overviewQuery = useDashboardOverview();
  const recentlyReadQuery = useRecentlyRead(recentlyReadPage);
  const commentsQuery = useUserComments(commentsPage);
  const savedPostsQuery = useSavedPosts(bookmarksPage);
  
  // Orchestrate the mutation here
  const unsaveMutation = useUnsavePost();
  const handleUnsave = (postId: string) => unsaveMutation.mutate(postId);

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
          <button className={`tab-btn ${activeTab === 'overview' ? 'active' : ''}`} onClick={() => setActiveTab('overview')}>
            <i className="fa-sharp fa-solid fa-grid-2"></i> Overview
          </button>
          <button className={`tab-btn ${activeTab === 'comments' ? 'active' : ''}`} onClick={() => setActiveTab('comments')}>
            <i className="fa-sharp fa-solid fa-comments"></i> My Comments
          </button>
          <button className={`tab-btn ${activeTab === 'bookmarks' ? 'active' : ''}`} onClick={() => setActiveTab('bookmarks')}>
            <i className="fa-sharp fa-solid fa-bookmark"></i> Saved Posts
          </button>
          <button className={`tab-btn ${activeTab === 'settings' ? 'active' : ''}`} onClick={() => setActiveTab('settings')}>
            <i className="fa-sharp fa-solid fa-gear"></i> Settings
          </button>
        </div>

        <div className="tab-content-wrapper">
          {activeTab === 'overview' && (
            <OverviewTab 
              overview={overviewQuery.data} 
              recentlyRead={recentlyReadQuery.data}
              isLoading={overviewQuery.isLoading || recentlyReadQuery.isLoading}
              onPageChange={setRecentlyReadPage}
            />
          )}
          {activeTab === 'comments' && (
            <CommentsTab 
              user={user}
              comments={commentsQuery.data}
              isLoading={commentsQuery.isLoading}
              onPageChange={setCommentsPage}
            />
          )}
          {activeTab === 'bookmarks' && (
            <BookmarksTab 
              savedPosts={savedPostsQuery.data}
              isLoading={savedPostsQuery.isLoading}
              onPageChange={setBookmarksPage}
              onUnsave={handleUnsave}
            />
          )}
          {activeTab === 'settings' && <SettingsTab />}
        </div>
      </div>
    </main>
  );
}