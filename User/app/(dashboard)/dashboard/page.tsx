'use client';

import { useState } from 'react';
import OverviewTab from '@/features/dashboard/components/OverviewTab';
// Import other tabs (Comments, Bookmarks, Settings) here later
import '@/styles/dashboard.css';

type TabId = 'overview' | 'comments' | 'bookmarks' | 'settings';

export default function DashboardPage() {
  const [activeTab, setActiveTab] = useState<TabId>('overview');

  return (
    <main className="dashboard-page">
      <div className="container dashboard-container">
        
        {/* Profile Header (Mocked for now, will be replaced by Profile hook later if needed) */}
        <section className="profile-header">
          <div className="profile-cover">
            <img src="https://picsum.photos/seed/coverphoto/1200/300" alt="Cover Photo" />
          </div>
          <div className="profile-info-wrapper">
            <div className="profile-avatar">
              <img src="https://picsum.photos/seed/regularuser/120/120" alt="User" />
              <span className="online-status"></span>
            </div>
            <div className="profile-details">
              <h1 className="profile-name">Your Dashboard</h1>
              <p className="profile-bio">Track your reading activity and manage your profile.</p>
            </div>
          </div>
        </section>

        {/* Tabs */}
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

        {/* Tab Content */}
        <div className="tab-content-wrapper">
          {activeTab === 'overview' && <OverviewTab />}
          {activeTab === 'comments' && <div className="tab-content active"><h3>Comments coming soon</h3></div>}
          {activeTab === 'bookmarks' && <div className="tab-content active"><h3>Saved Posts coming soon</h3></div>}
          {activeTab === 'settings' && <div className="tab-content active"><h3>Settings coming soon</h3></div>}
        </div>
        
      </div>
    </main>
  );
}