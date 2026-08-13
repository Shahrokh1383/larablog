'use client';

import { useState, useEffect } from 'react';
import Link from 'next/link';
import type { AboutData } from '@/features/about';

interface FooterProps {
  settings?: AboutData['settings'];
}

export default function Footer({ settings }: FooterProps) {
  const [isVisible, setIsVisible] = useState(false);
  const currentYear = new Date().getFullYear();

  const toggleVisibility = () => {
    if (window.scrollY > 300) setIsVisible(true);
    else setIsVisible(false);
  };

  const scrollToTop = () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  };

  useEffect(() => {
    window.addEventListener('scroll', toggleVisibility);
    return () => window.removeEventListener('scroll', toggleVisibility);
  }, []);

  const phone = settings?.call_us_phone;
  const emails = settings?.call_us_emails || [];
  const address = settings?.visit_address;
  const links = settings?.social_links || {};

  const socialConfig = [
    { key: 'twitter', icon: 'fa-x-twitter', label: 'Twitter' },
    { key: 'linkedin', icon: 'fa-linkedin-in', label: 'LinkedIn' },
    { key: 'github', icon: 'fa-github', label: 'GitHub' },
    { key: 'youtube', icon: 'fa-youtube', label: 'YouTube' },
    { key: 'discord', icon: 'fa-discord', label: 'Discord' },
    { key: 'instagram', icon: 'fa-instagram', label: 'Instagram' },
  ];

  return (
    <>
      <footer className="site-footer">
        <div className="footer-top">
          <div className="container">
            <div className="row g-4">
              <div className="col-lg-4 col-md-6">
                <div className="footer-about">
                  <Link href="/" className="footer-logo">
                    <i className="fa-sharp fa-solid fa-blog"></i>
                    <span>Lara<span className="logo-accent">Blog</span></span>
                  </Link>
                  <p className="footer-about-text">
                    LaraBlog is a professional platform for sharing knowledge, ideas, and stories.
                    We are committed to quality content and exceptional user experience.
                  </p>
                  <div className="footer-social">
                    {socialConfig.map(({ key, icon, label }) => {
                      const href = links[key as keyof typeof links];
                      if (!href) return null;
                      return (
                        <a 
                          key={key} 
                          href={href} 
                          target="_blank" 
                          rel="noopener noreferrer" 
                          className="social-link" 
                          aria-label={label}
                        >
                          <i className={`fa-brands ${icon}`}></i>
                        </a>
                      );
                    })}
                  </div>
                </div>
              </div>

              <div className="col-lg-2 col-md-6">
                <h4 className="footer-heading">Quick Links</h4>
                <ul className="footer-links">
                  <li><Link href="/">Home</Link></li>
                  <li><Link href="/category">Categories</Link></li>
                  <li><Link href="/tags">Tags</Link></li>
                  <li><Link href="/authors">Authors</Link></li>
                </ul>
              </div>

              <div className="col-lg-4 col-md-6">
                <h4 className="footer-heading">Contact Us</h4>
                <ul className="footer-contact">
                  <li>
                    <i className="fa-sharp fa-solid fa-location-dot"></i>
                    <span>{address || 'Address not available'}</span>
                  </li>
                  <li>
                    <i className="fa-sharp fa-solid fa-envelope"></i>
                    {emails.length > 0 ? (
                      <span style={{ display: 'flex', flexDirection: 'column' }}>
                        {emails.map((email, i) => (
                          <a key={i} href={`mailto:${email}`}>{email}</a>
                        ))}
                      </span>
                    ) : (
                      <span>Email not available</span>
                    )}
                  </li>
                  <li>
                    <i className="fa-sharp fa-solid fa-phone"></i>
                    {phone ? <a href={`tel:${phone}`}>{phone}</a> : <span>Phone not available</span>}
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </div>

        <div className="footer-bottom">
          <div className="container">
            <div className="footer-bottom-content">
              <p className="footer-copyright">
                © {currentYear} <span>LaraBlog</span>. All rights reserved.
              </p>
              <div className="footer-bottom-links">
                <a href="/privacy">Privacy Policy</a>
                <span className="divider">|</span>
                <a href="/terms">Terms & Conditions</a>
              </div>
            </div>
          </div>
        </div>
      </footer>

      <button className={`back-to-top ${isVisible ? 'visible' : ''}`} id="backToTop" aria-label="Back to top" onClick={scrollToTop}>
        <i className="fa-sharp fa-solid fa-arrow-up"></i>
      </button>
    </>
  );
}