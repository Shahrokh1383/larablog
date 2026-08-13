import type { AboutData } from '@/features/about';

interface ContactMapProps {
  socialLinks?: AboutData['settings']['social_links'];
}

export default function ContactMap({ socialLinks }: ContactMapProps) {
  const links = socialLinks || {};
  
  const socialConfig = [
    { key: 'twitter', icon: 'fa-x-twitter', label: 'Twitter' },
    { key: 'linkedin', icon: 'fa-linkedin-in', label: 'LinkedIn' },
    { key: 'github', icon: 'fa-github', label: 'GitHub' },
    { key: 'instagram', icon: 'fa-instagram', label: 'Instagram' },
    { key: 'youtube', icon: 'fa-youtube', label: 'YouTube' },
    { key: 'discord', icon: 'fa-discord', label: 'Discord' },
  ];

  return (
    <div className="contact-map-wrapper">
      <div className="map-placeholder">
        <div className="map-inner">
          <i className="fa-sharp fa-solid fa-map-pin map-pin"></i>
          <div className="map-pulse"></div>
        </div>
        <p className="map-caption">We are here! 🌍</p>
      </div>
      <div className="map-details">
        <h4>Office Hours</h4>
        <ul className="hours-list">
          <li><span>Monday - Friday</span> <span>9:00 AM – 5:00 PM</span></li>
          <li><span>Saturday</span> <span>10:00 AM – 2:00 PM</span></li>
          <li><span>Sunday</span> <span>Closed</span></li>
        </ul>
        <div className="social-links-inline">
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
  );
}