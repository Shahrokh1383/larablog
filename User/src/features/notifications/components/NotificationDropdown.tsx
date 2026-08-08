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

  const handleToggle = () => {
    const newIsOpen = !isOpen;
    setIsOpen(newIsOpen);
    
    // Mark as read only when opening, and delay it so user can see them
    if (newIsOpen && unreadCount > 0) {
      setTimeout(() => {
        onMarkAsRead();
      }, 1500);
    }
  };

  return (
    <div className="user-dropdown">
      <button className="btn-icon" aria-label="Notifications" onClick={handleToggle}>
        <i className="fa-sharp fa-solid fa-bell"></i>
        {unreadCount > 0 && (
          <span className="notification-badge">{unreadCount}</span>
        )}
      </button>
      {isOpen && (
        <div className="notification-dropdown-menu">
          <h6 className="notification-header">Notifications</h6>
          <div className="notification-list">
            {notifications.length === 0 ? (
              <p className="notification-empty">No new notifications.</p>
            ) : (
              notifications.map((notif) => (
                <Link
                  key={notif.id}
                  href={`/post/${notif.post_id}`} 
                  className="notification-item"
                  onClick={() => setIsOpen(false)}
                >
                  <span className="notification-message">{notif.message}</span>
                </Link>
              ))
            )}
          </div>
        </div>
      )}
    </div>
  );
}