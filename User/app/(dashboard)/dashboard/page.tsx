'use client';

import { useState } from 'react';
import { useAuth } from '@/features/auth/context/AuthContext';
import { useDashboardOverview } from '@/features/dashboard/hooks/useDashboardOverview';
import DashboardHeader from '@/features/dashboard/components/DashboardHeader';
import OverviewTab from '@/features/dashboard/components/OverviewTab';
import '@/styles/dashboard.css';

type TabId = 'overview' | 'comments' | 'bookmarks' | 'settings';

export default function DashboardPage() {
  const [activeTab, setActiveTab] = useState<TabId>('overview');
  const { user } = useAuth();
  const { data: overview } = useDashboardOverview();

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
        
        <DashboardHeader user={user} overview={overview} />

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
          {activeTab === 'overview' && <OverviewTab />}
          {activeTab === 'comments' && <div className="tab-content active"><h3>Comments coming soon</h3></div>}
          {activeTab === 'bookmarks' && <div className="tab-content active"><h3>Saved Posts coming soon</h3></div>}
          {activeTab === 'settings' && <div className="tab-content active"><h3>Settings coming soon</h3></div>}
        </div>
        
      </div>
    </main>
  );
}