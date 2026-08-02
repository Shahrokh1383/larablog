'use client';

import Link from 'next/link';
import { useState, useEffect } from 'react';
import { useAuth } from '@/features/auth/context/AuthContext';
import { useTheme } from '@/providers/ThemeProvider';

export default function Header() {
  const [isMenuOpen, setIsMenuOpen] = useState(false);
  const [isSearchOpen, setIsSearchOpen] = useState(false);
  const [isScrolled, setIsScrolled] = useState(false);
  const { isAuthenticated, isLoading } = useAuth();
  const { theme, toggleTheme } = useTheme();

  useEffect(() => {
    const handleScroll = () => setIsScrolled(window.scrollY > 10);
    window.addEventListener('scroll', handleScroll);
    return () => window.removeEventListener('scroll', handleScroll);
  }, []);

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
              <li className="nav-item"><Link href="/" className="nav-link active">Home</Link></li>
              <li className="nav-item"><Link href="/category" className="nav-link">Categories</Link></li>
              <li className="nav-item"><Link href="/tags" className="nav-link">Tags</Link></li>
              <li className="nav-item"><Link href="/authors" className="nav-link">Authors</Link></li>
              <li className="nav-item"><Link href="/about" className="nav-link">About</Link></li>
              <li className="nav-item"><Link href="/contact" className="nav-link">Contact</Link></li>
            </ul>

            <div className="nav-actions">
              <button className="btn-icon" aria-label="Search" onClick={() => setIsSearchOpen(true)}>
                <i className="fa-sharp fa-solid fa-magnifying-glass"></i>
              </button>
              
              <button className="btn-icon theme-toggle" aria-label="Toggle theme" onClick={toggleTheme}>
                <i className={`fa-sharp fa-solid ${theme === 'light' ? 'fa-moon' : 'fa-sun'}`}></i>
              </button>

              {isLoading ? (
                <div className="spinner-border spinner-border-sm text-primary" role="status">
                  <span className="visually-hidden">Loading...</span>
                </div>
              ) : isAuthenticated ? (
                <Link href="/dashboard" className="btn-dashboard d-none d-lg-inline-flex">
                  <i className="fa-sharp fa-solid fa-gauge-high"></i>
                  <span>Dashboard</span>
                </Link>
              ) : (
                <Link href="/login" className="btn-dashboard d-none d-lg-inline-flex">
                  <i className="fa-sharp fa-solid fa-right-to-bracket"></i>
                  <span>Sign In</span>
                </Link>
              )}

              <button className="hamburger" id="hamburgerBtn" aria-label="Menu" aria-expanded={isMenuOpen} onClick={() => setIsMenuOpen(!isMenuOpen)}>
                <span className="hamburger-line"></span>
                <span className="hamburger-line"></span>
                <span className="hamburger-line"></span>
              </button>
            </div>
          </div>
        </nav>

        {/* Search Overlay */}
        {isSearchOpen && (
          <div className="search-overlay active" id="searchOverlay">
            <div className="search-overlay-content">
              <button className="search-close" onClick={() => setIsSearchOpen(false)}>
                <i className="fa-sharp fa-solid fa-xmark"></i>
              </button>
              <form className="search-form" action="/search" method="get">
                <input type="text" name="q" className="search-input" placeholder="Search articles, topics, authors..." autoComplete="off" autoFocus />
                <button type="submit" className="search-submit">
                  <i className="fa-sharp fa-solid fa-magnifying-glass"></i>
                </button>
              </form>
            </div>
          </div>
        )}
      </header>

      {/* Mobile Menu Overlay */}
      <div className={`mobile-menu-overlay ${isMenuOpen ? 'active' : ''}`} id="mobileMenuOverlay">
        <div className="mobile-menu-bg" onClick={() => setIsMenuOpen(false)}></div>
        <div className="mobile-menu-content">
          <ul className="mobile-nav-links">
            <li className="mobile-nav-item"><Link href="/" className="mobile-nav-link active" onClick={() => setIsMenuOpen(false)}>Home</Link></li>
            <li className="mobile-nav-item"><Link href="/category" className="mobile-nav-link" onClick={() => setIsMenuOpen(false)}>Categories</Link></li>
            <li className="mobile-nav-item"><Link href="/tags" className="mobile-nav-link" onClick={() => setIsMenuOpen(false)}>Tags</Link></li>
            <li className="mobile-nav-item"><Link href="/authors" className="mobile-nav-link" onClick={() => setIsMenuOpen(false)}>Authors</Link></li>
            <li className="mobile-nav-item"><Link href="/about" className="mobile-nav-link" onClick={() => setIsMenuOpen(false)}>About</Link></li>
            <li className="mobile-nav-item"><Link href="/contact" className="mobile-nav-link" onClick={() => setIsMenuOpen(false)}>Contact</Link></li>
            <li className="mobile-nav-item">
              <Link href={isAuthenticated ? "/dashboard" : "/login"} className="mobile-nav-link mobile-dashboard-link" onClick={() => setIsMenuOpen(false)}>
                <i className={`fa-sharp fa-solid ${isAuthenticated ? 'fa-gauge-high' : 'fa-right-to-bracket'}`}></i>
                {isAuthenticated ? 'Dashboard' : 'Sign In'}
              </Link>
            </li>
          </ul>
        </div>
      </div>
    </>
  );
}