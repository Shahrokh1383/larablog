export default function ContactMap() {
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
          <a href="#" className="social-link" aria-label="Twitter"><i className="fa-brands fa-x-twitter"></i></a>
          <a href="#" className="social-link" aria-label="LinkedIn"><i className="fa-brands fa-linkedin-in"></i></a>
          <a href="#" className="social-link" aria-label="GitHub"><i className="fa-brands fa-github"></i></a>
          <a href="#" className="social-link" aria-label="Instagram"><i className="fa-brands fa-instagram"></i></a>
        </div>
      </div>
    </div>
  );
}