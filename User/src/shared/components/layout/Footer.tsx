'use client';

import { useState, useEffect } from 'react';
import Link from 'next/link';

export default function Footer() {
  const [isVisible, setIsVisible] = useState(false);

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
                    <a href="#" className="social-link" aria-label="Twitter"><i className="fa-brands fa-x-twitter"></i></a>
                    <a href="#" className="social-link" aria-label="LinkedIn"><i className="fa-brands fa-linkedin-in"></i></a>
                    <a href="#" className="social-link" aria-label="GitHub"><i className="fa-brands fa-github"></i></a>
                    <a href="#" className="social-link" aria-label="YouTube"><i className="fa-brands fa-youtube"></i></a>
                    <a href="#" className="social-link" aria-label="Discord"><i className="fa-brands fa-discord"></i></a>
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
                    <span>123 Innovation Drive, Tech City, CA 94043</span>
                  </li>
                  <li>
                    <i className="fa-sharp fa-solid fa-envelope"></i>
                    <a href="mailto:LaraBlog@gmail.com">LaraBlog@gmail.com</a>
                  </li>
                  <li>
                    <i className="fa-sharp fa-solid fa-phone"></i>
                    <a href="tel:+1234567890">+1 (234) 567-890</a>
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
                © 2024 <span>LaraBlog</span>. All rights reserved.
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