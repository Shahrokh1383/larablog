'use client';

import Link from 'next/link';
import { useState } from 'react';
import { NotificationItem } from '../api/notificationsApi';

interface NotificationDropdownProps {
  notifications: NotificationItem[];
  unreadCount: number;
  onMarkAsRead: () => void;
}

export default function NotificationDropdown({
  notifications,
  unreadCount,
  onMarkAsRead,
}: NotificationDropdownProps) {
  const [isOpen, setIsOpen] = useState(false);

  const handleToggle = (e: React.MouseEvent) => {
    e.stopPropagation();
    setIsOpen(!isOpen);
  };

  const handleClose = () => setIsOpen(false);

  return (
    <div className="user-dropdown">
      <button className="btn-icon" aria-label="Notifications" onClick={handleToggle}>
        <i className="fa-sharp fa-solid fa-bell"></i>
        {unreadCount > 0 && (
          <span className="notification-badge">{unreadCount}</span>
        )}
      </button>

      {isOpen && (
        <>
          <div className="notification-backdrop" onClick={handleClose} />
          
          <div className="notification-dropdown-menu" onClick={(e) => e.stopPropagation()}>
            <div className="notification-header d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
              <h6 className="mb-0">Notifications</h6>
              {unreadCount > 0 && (
                <button 
                  className="btn btn-sm btn-link text-decoration-none p-0 text-primary" 
                  onClick={onMarkAsRead}
                >
                  Mark all as read
                </button>
              )}
            </div>
            <div className="notification-list">
              {notifications.length === 0 ? (
                <p className="notification-empty text-center py-4 text-muted">No notifications yet.</p>
              ) : (
                notifications.map((notif) => (
                  <Link
                    key={notif.id}
                    // Fallback to ID if slug is missing (for legacy DB records)
                    href={notif.post_slug ? `/post/${notif.post_slug}` : (notif.post_id ? `/post/${notif.post_id}` : '#')} 
                    className={`notification-item d-block px-3 py-2 ${!notif.read_at ? 'unread bg-light' : ''}`}
                    onClick={handleClose}
                  >
                    <span className="notification-message fw-medium">{notif.message}</span>
                    <small className="notification-time text-muted d-block mt-1">
                      {new Date(notif.created_at).toLocaleString()}
                    </small>
                  </Link>
                ))
              )}
            </div>
          </div>
        </>
      )}
    </div>
  );
}