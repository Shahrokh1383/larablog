'use client';

import Link from 'next/link';
import { useState, useEffect, useRef } from 'react';
import { usePathname } from 'next/navigation';
import { useAuth } from '@/features/auth/context/AuthContext';
import { useLogout } from '@/features/auth/hooks/useLogout';
import { useProfile } from '@/features/profile/hooks/useProfile';
import { useTheme } from '@/providers/ThemeProvider';
import { useNotifications } from '@/features/notifications/hooks/useNotifications';
import NotificationDropdown from '@/features/notifications/components/NotificationDropdown';

const DEFAULT_AVATAR = `data:image/svg+xml;utf8,${encodeURIComponent(`
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" width="100" height="100">
  <circle cx="50" cy="50" r="50" fill="#E2E8F0"/>
  <circle cx="50" cy="40" r="20" fill="#94A3B8"/>
  <ellipse cx="50" cy="85" rx="30" ry="25" fill="#94A3B8"/>
</svg>`)}`;

export default function Header() {
  const [isMenuOpen, setIsMenuOpen] = useState(false);
  const [isSearchOpen, setIsSearchOpen] = useState(false);
  const [isScrolled, setIsScrolled] = useState(false);
  const [dropdownOpen, setDropdownOpen] = useState(false);
  const { user, isAuthenticated, isLoading } = useAuth();
  const { data: profile } = useProfile({ enabled: isAuthenticated && !!user });
  const { logout } = useLogout();
  const { theme, toggleTheme } = useTheme();
  const pathname = usePathname();
  const dropdownRef = useRef<HTMLDivElement>(null);
  
  // New React Query based notifications hook
  const { notifications, unreadCount, markSingleAsRead, markAllAsRead } = useNotifications();

  useEffect(() => {
    const handleScroll = () => setIsScrolled(window.scrollY > 10);
    window.addEventListener('scroll', handleScroll);
    return () => window.removeEventListener('scroll', handleScroll);
  }, []);

  // Close dropdown on outside click
  useEffect(() => {
    const handleClickOutside = (e: MouseEvent) => {
      if (dropdownRef.current && !dropdownRef.current.contains(e.target as Node)) {
        setDropdownOpen(false);
      }
    };
    document.addEventListener('mousedown', handleClickOutside);
    return () => document.removeEventListener('mousedown', handleClickOutside);
  }, []);

  // Active link helper
  const isActive = (href: string) => {
    if (href === '/') return pathname === '/';
    return pathname.startsWith(href);
  };

  const handleLogout = async () => {
    await logout();
    setDropdownOpen(false);
  };

  const avatarSrc = profile?.avatar || user?.avatar || DEFAULT_AVATAR;

  return (
    <>
      <header className={`site-header ${isScrolled ? 'scrolled' : ''}`} id="siteHeader">
        <nav className="navbar-custom">
          <div className="nav-container">
            <Link href="/" className="nav-logo">
              <span className="logo-icon">
                <i className="fa-sharp fa-solid fa-blog"></i>
              </span>
              <span className="logo-text">Lara<span className="logo-accent">Blog</span></span>
            </Link>

            <ul className="nav-links" id="navLinks">
              <li className="nav-item"><Link href="/" className={`nav-link ${isActive('/') ? 'active' : ''}`}>Home</Link></li>
              <li className="nav-item"><Link href="/category" className={`nav-link ${isActive('/category') ? 'active' : ''}`}>Categories</Link></li>
              <li className="nav-item"><Link href="/tags" className={`nav-link ${isActive('/tags') ? 'active' : ''}`}>Tags</Link></li>
              <li className="nav-item"><Link href="/authors" className={`nav-link ${isActive('/authors') ? 'active' : ''}`}>Authors</Link></li>
              <li className="nav-item"><Link href="/about" className={`nav-link ${isActive('/about') ? 'active' : ''}`}>About</Link></li>
              <li className="nav-item"><Link href="/contact" className={`nav-link ${isActive('/contact') ? 'active' : ''}`}>Contact</Link></li>
            </ul>

            <div className="nav-actions">
              <button className="btn-icon" aria-label="Search" onClick={() => setIsSearchOpen(true)}>
                <i className="fa-sharp fa-solid fa-magnifying-glass"></i>
              </button>

              {isAuthenticated && (
                <NotificationDropdown 
                  notifications={notifications} 
                  unreadCount={unreadCount} 
                  onMarkSingleAsRead={markSingleAsRead}
                  onMarkAllAsRead={markAllAsRead}
                />
              )}

              <button className="btn-icon theme-toggle" aria-label="Toggle theme" onClick={toggleTheme}>
                <i className={`fa-sharp fa-solid ${theme === 'light' ? 'fa-moon' : 'fa-sun'}`}></i>
              </button>

              {isLoading ? (
                <div className="spinner-border spinner-border-sm text-primary" role="status">
                  <span className="visually-hidden">Loading...</span>
                </div>
              ) : isAuthenticated && user ? (
                <div className="user-dropdown" ref={dropdownRef}>
                  <button
                    className="btn-user"
                    onClick={() => setDropdownOpen(!dropdownOpen)}
                    aria-expanded={dropdownOpen}
                    aria-haspopup="true"
                  >
                    <img src={avatarSrc} alt={user.name} className="user-avatar" />
                    <span className="user-name d-none d-lg-inline">{user.name}</span>
                    <i className={`fa-sharp fa-solid fa-chevron-down dropdown-arrow ${dropdownOpen ? 'open' : ''}`}></i>
                  </button>
                  {dropdownOpen && (
                    <div className="dropdown-menu show">
                      <Link href="/dashboard" className="dropdown-item" onClick={() => setDropdownOpen(false)}>
                        <i className="fa-sharp fa-solid fa-gauge-high me-2"></i>Dashboard
                      </Link>
                      <button className="dropdown-item" onClick={handleLogout}>
                        <i className="fa-sharp fa-solid fa-right-from-bracket me-2"></i>Logout
                      </button>
                    </div>
                  )}
                </div>
              ) : (
                <Link href="/login" className="btn-dashboard d-none d-lg-inline-flex">
                  <i className="fa-sharp fa-solid fa-right-to-bracket"></i>
                  <span>Sign In</span>
                </Link>
              )}

              <button
                className={`hamburger ${isMenuOpen ? 'active' : ''}`}
                id="hamburgerBtn"
                aria-label="Menu"
                aria-expanded={isMenuOpen}
                onClick={() => setIsMenuOpen(!isMenuOpen)}
              >
                <span className="hamburger-line"></span>
                <span className="hamburger-line"></span>
                <span className="hamburger-line"></span>
              </button>
            </div>
          </div>
        </nav>

        {/* Search Overlay */}
        <div className={`search-overlay ${isSearchOpen ? 'active' : ''}`} id="searchOverlay">
          <div className="search-overlay-content">
            <button className="search-close" id="searchClose" onClick={() => setIsSearchOpen(false)}>
              <i className="fa-sharp fa-solid fa-xmark"></i>
            </button>
            <form className="search-form" action="/search" method="get">
              <input type="text" name="q" className="search-input" placeholder="Search articles, topics, authors..." autoComplete="off" autoFocus={isSearchOpen} />
              <button type="submit" className="search-submit">
                <i className="fa-sharp fa-solid fa-magnifying-glass"></i>
              </button>
            </form>
          </div>
        </div>
      </header>

      {/* Mobile Menu Overlay */}
      <div className={`mobile-menu-overlay ${isMenuOpen ? 'active' : ''}`} id="mobileMenuOverlay">
        <div className="mobile-menu-bg" onClick={() => setIsMenuOpen(false)}></div>
        <div className="mobile-menu-content">
          <ul className="mobile-nav-links">
            <li className="mobile-nav-item"><Link href="/" className={`mobile-nav-link ${isActive('/') ? 'active' : ''}`} onClick={() => setIsMenuOpen(false)}>Home</Link></li>
            <li className="mobile-nav-item"><Link href="/category" className={`mobile-nav-link ${isActive('/category') ? 'active' : ''}`} onClick={() => setIsMenuOpen(false)}>Categories</Link></li>
            <li className="mobile-nav-item"><Link href="/tags" className={`mobile-nav-link ${isActive('/tags') ? 'active' : ''}`} onClick={() => setIsMenuOpen(false)}>Tags</Link></li>
            <li className="mobile-nav-item"><Link href="/authors" className={`mobile-nav-link ${isActive('/authors') ? 'active' : ''}`} onClick={() => setIsMenuOpen(false)}>Authors</Link></li>
            <li className="mobile-nav-item"><Link href="/about" className={`mobile-nav-link ${isActive('/about') ? 'active' : ''}`} onClick={() => setIsMenuOpen(false)}>About</Link></li>
            <li className="mobile-nav-item"><Link href="/contact" className={`mobile-nav-link ${isActive('/contact') ? 'active' : ''}`} onClick={() => setIsMenuOpen(false)}>Contact</Link></li>
            {!isAuthenticated && (
              <li className="mobile-nav-item">
                <Link href="/login" className="mobile-nav-link mobile-dashboard-link" onClick={() => setIsMenuOpen(false)}>
                  <i className="fa-sharp fa-solid fa-right-to-bracket"></i>
                  Sign In
                </Link>
              </li>
            )}
          </ul>
        </div>
      </div>
    </>
  );
}